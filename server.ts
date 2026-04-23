import express from 'express';
import path from 'path';
import { fileURLToPath } from 'url';
import fs from 'fs';
import { createServer as createViteServer } from 'vite';
import sqlite3 from 'sqlite3';
import { open } from 'sqlite';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

async function initDb() {
  const db = await open({
    filename: './database.sqlite',
    driver: sqlite3.Database
  });

  // สร้างตารางพื้นฐาน
  await db.exec(`
    CREATE TABLE IF NOT EXISTS schools (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      code TEXT UNIQUE NOT NULL,
      is_approved INTEGER DEFAULT 0,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT UNIQUE NOT NULL,
      password TEXT NOT NULL,
      name TEXT NOT NULL,
      role TEXT NOT NULL, -- super_admin, admin, teacher
      school_id INTEGER,
      is_approved INTEGER DEFAULT 0,
      FOREIGN KEY(school_id) REFERENCES schools(id)
    );

    CREATE TABLE IF NOT EXISTS teachers (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      position TEXT,
      school_id INTEGER,
      FOREIGN KEY(school_id) REFERENCES schools(id)
    );

    CREATE TABLE IF NOT EXISTS subjects (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      code TEXT NOT NULL,
      name TEXT NOT NULL,
      level TEXT,
      hours_per_week INTEGER DEFAULT 1,
      is_double INTEGER DEFAULT 0,
      school_id INTEGER,
      FOREIGN KEY(school_id) REFERENCES schools(id)
    );

    CREATE TABLE IF NOT EXISTS rooms (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      school_id INTEGER,
      FOREIGN KEY(school_id) REFERENCES schools(id)
    );

    CREATE TABLE IF NOT EXISTS classrooms (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      level TEXT NOT NULL,
      name TEXT NOT NULL,
      school_id INTEGER,
      FOREIGN KEY(school_id) REFERENCES schools(id)
    );

    CREATE TABLE IF NOT EXISTS teaching_load (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      teacher_id INTEGER,
      subject_id INTEGER,
      classroom_id INTEGER,
      room_id INTEGER,
      hours_per_week INTEGER DEFAULT 2,
      period_type TEXT DEFAULT 'single', -- single, double
      fixed_slots TEXT, -- JSON array
      allowed_slots TEXT, -- JSON array
      school_id INTEGER,
      FOREIGN KEY(teacher_id) REFERENCES teachers(id),
      FOREIGN KEY(subject_id) REFERENCES subjects(id),
      FOREIGN KEY(classroom_id) REFERENCES classrooms(id),
      FOREIGN KEY(room_id) REFERENCES rooms(id)
    );

    CREATE TABLE IF NOT EXISTS periods (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      school_id INTEGER,
      period_number INTEGER NOT NULL,
      start_time TEXT,
      end_time TEXT,
      type TEXT DEFAULT 'normal', -- normal, break, activity
      FOREIGN KEY(school_id) REFERENCES schools(id)
    );

    CREATE TABLE IF NOT EXISTS timetable (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      school_id INTEGER,
      teacher_id INTEGER,
      subject_id INTEGER,
      classroom_id INTEGER,
      room_id INTEGER,
      day INTEGER, -- 1-5
      period INTEGER,
      FOREIGN KEY(teacher_id) REFERENCES teachers(id),
      FOREIGN KEY(subject_id) REFERENCES subjects(id),
      FOREIGN KEY(classroom_id) REFERENCES classrooms(id),
      FOREIGN KEY(room_id) REFERENCES rooms(id)
    );
  `);

  // สร้าง Super Admin ถ้ายังไม่มี
  const superAdmin = await db.get("SELECT * FROM users WHERE role = 'super_admin'");
  if (!superAdmin) {
    await db.run(
      "INSERT INTO users (username, password, name, role, is_approved) VALUES (?, ?, ?, ?, ?)",
      ['admin', '123456', 'Super Admin', 'super_admin', 1]
    );
    
    // Seed dummy data for school_id 1
    const school_id = 1;
    await db.run("INSERT OR IGNORE INTO schools (id, name, code, is_approved) VALUES (?, ?, ?, ?)", [school_id, 'โรงเรียนตัวอย่าง', 'TEST001', 1]);
    
    const teachers = [
        ['สมชาย ใจดี', 'ครูชำนาญการ'],
        ['สมศรี มีสุข', 'ครูประจำการ'],
        ['บุญมี มานะ', 'ครูอัตราจ้าง']
    ];
    for (const [n, p] of teachers) {
        await db.run("INSERT INTO teachers (name, position, school_id) VALUES (?, ?, ?)", [n, p, school_id]);
    }

    const classrooms = [
        ['ป.1', '1'], ['ป.1', '2'], ['ป.2', '1']
    ];
    for (const [l, n] of classrooms) {
        await db.run("INSERT INTO classrooms (level, name, school_id) VALUES (?, ?, ?)", [l, n, school_id]);
    }

    const subjects = [
        ['ท11101', 'ภาษาไทย', 'ป.1', 5, 0],
        ['ค11101', 'คณิตศาสตร์', 'ป.1', 5, 0],
        ['ว11101', 'วิทยาศาสตร์', 'ป.1', 3, 0],
        ['ส11101', 'สังคมศึกษา', 'ป.1', 2, 0]
    ];
    for (const [c, n, l, h, d] of subjects) {
        await db.run("INSERT INTO subjects (code, name, level, hours_per_week, is_double, school_id) VALUES (?, ?, ?, ?, ?, ?)", [c, n, l, h, d, school_id]);
    }

    // Default periods
    const defaults = [
        {n: 1, s: '08:30', e: '09:20', t: 'normal'},
        {n: 2, s: '09:20', e: '10:10', t: 'normal'},
        {n: 3, s: '10:10', e: '11:00', t: 'normal'},
        {n: 4, s: '11:00', e: '11:50', t: 'normal'},
        {n: 5, s: '11:50', e: '12:50', t: 'break'},
        {n: 6, s: '12:50', e: '13:40', t: 'normal'},
        {n: 7, s: '13:40', e: '14:30', t: 'normal'},
        {n: 8, s: '14:30', e: '15:20', t: 'normal'}
    ];
    for (const d of defaults) {
        await db.run("INSERT INTO periods (school_id, period_number, start_time, end_time, type) VALUES (?, ?, ?, ?, ?)", [school_id, d.n, d.s, d.e, d.t]);
    }
  }

  return db;
}

async function startServer() {
  const app = express();
  const PORT = 3000;
  const db = await initDb();

  app.use(express.json());

  // API Routes
  app.post('/api/login', async (req, res) => {
    const { username, password } = req.body;
    // For preview purposes, we simulate the MySQL login using the existing SQLite
    const user = await db.get("SELECT * FROM users WHERE username = ? AND password = ?", [username, password]);
    if (user) {
      res.json(user);
    } else {
      res.status(401).json({ error: 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง (ลองใช้ admin / 123456)' });
    }
  });

  // Main Management API (Mocking PHP api/manage.php)
  app.all('/api/manage.php', async (req, res) => {
    const action = req.query.action;
    const school_id = 1; // Simulated school_id for preview

    try {
      switch (action) {
        case 'teachers_list':
          const teachers = await db.all("SELECT * FROM teachers WHERE school_id = ? ORDER BY name ASC", [school_id]);
          res.json(teachers);
          break;
        case 'classrooms_list':
          const classrooms = await db.all("SELECT * FROM classrooms WHERE school_id = ? ORDER BY level, name ASC", [school_id]);
          res.json(classrooms);
          break;
        case 'rooms_list':
          const rooms = await db.all("SELECT * FROM rooms WHERE school_id = ? ORDER BY name ASC", [school_id]);
          res.json(rooms);
          break;
        case 'subjects_list':
          const subjects = await db.all("SELECT * FROM subjects WHERE school_id = ? ORDER BY level, code ASC", [school_id]);
          res.json(subjects);
          break;
        case 'subjects_by_level':
          const subByLevel = await db.all("SELECT * FROM subjects WHERE school_id = ? AND level = ? ORDER BY code ASC", [school_id, req.query.level]);
          res.json(subByLevel);
          break;
        case 'teaching_load_list':
          const loadList = await db.all(`
            SELECT tl.*, s.name as subject_name, s.code as subject_code, c.name as classroom_name, c.level as classroom_level, r.name as room_name
            FROM teaching_load tl
            JOIN subjects s ON tl.subject_id = s.id
            JOIN classrooms c ON tl.classroom_id = c.id
            LEFT JOIN rooms r ON tl.room_id = r.id
            WHERE tl.school_id = ? AND tl.teacher_id = ?
          `, [school_id, req.query.teacher_id]);
          res.json(loadList);
          break;
        case 'teaching_load_add':
          const { teacher_id, subject_id, classroom_id, room_id, hours_per_week, period_type } = req.body;
          await db.run(
            "INSERT INTO teaching_load (teacher_id, subject_id, classroom_id, room_id, hours_per_week, period_type, school_id) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [teacher_id, subject_id, classroom_id, room_id, hours_per_week, period_type, school_id]
          );
          res.json({ success: true });
          break;
        case 'teaching_load_delete':
          await db.run("DELETE FROM teaching_load WHERE id = ? AND school_id = ?", [req.query.id, school_id]);
          res.json({ success: true });
          break;
        case 'periods_list':
          let periods = await db.all("SELECT * FROM periods WHERE school_id = ? ORDER BY period_number ASC", [school_id]);
          if (periods.length === 0) {
            // Seed default periods if empty
            const defaults = [
                {n: 1, s: '08:30', e: '09:20', t: 'normal'},
                {n: 2, s: '09:20', e: '10:10', t: 'normal'},
                {n: 3, s: '10:10', e: '11:00', t: 'normal'},
                {n: 4, s: '11:00', e: '11:50', t: 'normal'},
                {n: 5, s: '11:50', e: '12:50', t: 'break'},
                {n: 6, s: '12:50', e: '13:40', t: 'normal'},
                {n: 7, s: '13:40', e: '14:30', t: 'normal'},
                {n: 8, s: '14:30', e: '15:20', t: 'normal'}
            ];
            for (const d of defaults) {
                await db.run("INSERT INTO periods (school_id, period_number, start_time, end_time, type) VALUES (?, ?, ?, ?, ?)", [school_id, d.n, d.s, d.e, d.t]);
            }
            periods = await db.all("SELECT * FROM periods WHERE school_id = ? ORDER BY period_number ASC", [school_id]);
          }
          res.json(periods);
          break;
        case 'get_teacher_timetable':
          const tTable = await db.all(`
            SELECT t.*, s.code as subject_code, s.name as subject_name, c.level as classroom_level, c.name as classroom_name, r.name as room_name, te.name as teacher_name
            FROM timetable t
            JOIN subjects s ON t.subject_id = s.id
            JOIN classrooms c ON t.classroom_id = c.id
            JOIN teachers te ON t.teacher_id = te.id
            LEFT JOIN rooms r ON t.room_id = r.id
            WHERE t.school_id = ? AND t.teacher_id = ?
          `, [school_id, req.query.teacher_id]);
          res.json(tTable);
          break;
        case 'get_classroom_timetable':
          const cTable = await db.all(`
            SELECT t.*, s.code as subject_code, s.name as subject_name, c.level as classroom_level, c.name as classroom_name, r.name as room_name, te.name as teacher_name
            FROM timetable t
            JOIN subjects s ON t.subject_id = s.id
            JOIN classrooms c ON t.classroom_id = c.id
            JOIN teachers te ON t.teacher_id = te.id
            LEFT JOIN rooms r ON t.room_id = r.id
            WHERE t.school_id = ? AND t.classroom_id = ?
          `, [school_id, req.query.classroom_id]);
          res.json(cTable);
          break;
        case 'get_room_timetable':
          const rTable = await db.all(`
            SELECT t.*, s.code as subject_code, s.name as subject_name, c.level as classroom_level, c.name as classroom_name, r.name as room_name, te.name as teacher_name
            FROM timetable t
            JOIN subjects s ON t.subject_id = s.id
            JOIN classrooms c ON t.classroom_id = c.id
            JOIN teachers te ON t.teacher_id = te.id
            LEFT JOIN rooms r ON t.room_id = r.id
            WHERE t.school_id = ? AND t.room_id = ?
          `, [school_id, req.query.room_id]);
          res.json(rTable);
          break;
        case 'save_teaching_load_slots':
          await db.run(
            `UPDATE teaching_load SET ${req.body.type === 'fixed' ? 'fixed_slots' : 'allowed_slots'} = ? WHERE id = ? AND school_id = ?`,
            [req.body.slots, req.body.load_id, school_id]
          );
          res.json({ success: true });
          break;
        case 'get_stats':
          const stats = {
            subjects: (await db.get("SELECT COUNT(*) as c FROM subjects WHERE school_id = ?", [school_id])).c,
            teachers: (await db.get("SELECT COUNT(*) as c FROM teachers WHERE school_id = ?", [school_id])).c,
            rooms: (await db.get("SELECT COUNT(*) as c FROM rooms WHERE school_id = ?", [school_id])).c,
            classrooms: (await db.get("SELECT COUNT(*) as c FROM classrooms WHERE school_id = ?", [school_id])).c,
            schools: (await db.get("SELECT COUNT(*) as c FROM schools")).c,
            users: (await db.get("SELECT COUNT(*) as c FROM users")).c
          };
          res.json(stats);
          break;
        case 'admin_schools_list':
          const sList = await db.all("SELECT * FROM schools ORDER BY created_at DESC");
          res.json(sList);
          break;
        case 'admin_school_approve':
          await db.run("UPDATE schools SET is_approved = 1 WHERE id = ?", [req.query.id]);
          await db.run("UPDATE users SET is_approved = 1 WHERE school_id = ?", [req.query.id]);
          res.json({ success: true });
          break;
        case 'admin_school_delete':
          await db.run("DELETE FROM schools WHERE id = ?", [req.query.id]);
          res.json({ success: true });
          break;
        case 'system_db_update':
        case 'system_sync':
          res.json({ success: true, message: 'ฐานข้อมูล SQLite เป็นปัจจุบันที่สุดแล้ว' });
          break;
        case 'auto_generate_timetable':
          try {
            await db.run("DELETE FROM timetable WHERE school_id = ?", [school_id]);
            const loads = await db.all("SELECT * FROM teaching_load WHERE school_id = ?", [school_id]);
            const allPs = await db.all("SELECT * FROM periods WHERE school_id = ? ORDER BY period_number ASC", [school_id]);
            const normalPs = allPs.filter(p => p.type === 'normal');
            
            let busyT = new Set();
            let busyC = new Set();
            let subCountDay = new Map(); // Tracking subject usage per day for each classroom
            let assigned = 0;
            const days = [1, 2, 3, 4, 5];

            const getSubCount = (d, c, s) => subCountDay.get(`${d}-${c}-${s}`) || 0;
            const incSubCount = (d, c, s) => subCountDay.set(`${d}-${c}-${s}`, getSubCount(d, c, s) + 1);

            // 1. Process Fixed Slots first
            for (const l of loads) {
              const fixed = JSON.parse(l.fixed_slots || '[]');
              for (const s of fixed) {
                await db.run("INSERT INTO timetable (school_id, teacher_id, subject_id, classroom_id, room_id, day, period) VALUES (?, ?, ?, ?, ?, ?, ?)",
                  [school_id, l.teacher_id, l.subject_id, l.classroom_id, l.room_id, s.day, s.period]);
                busyT.add(`${s.day}-${s.period}-${l.teacher_id}`);
                busyC.add(`${s.day}-${s.period}-${l.classroom_id}`);
                incSubCount(s.day, l.classroom_id, l.subject_id);
                assigned++;
              }
              l.remaining = (l.hours_per_week || 2) - fixed.length;
            }

            // 2. Random Distribution
            const shuffleArray = (arr) => [...arr].sort(() => Math.random() - 0.5);
            
            // Try multiple passes to fill slots
            for (let pass = 1; pass <= 3; pass++) {
              const shuffledLoads = shuffleArray(loads);
              for (const l of shuffledLoads) {
                if (l.remaining <= 0) continue;

                const allowed = JSON.parse(l.allowed_slots || '[]');
                const isDouble = l.period_type === 'double';
                const maxPerDay = Math.ceil((l.hours_per_week || 2) / 5) + (pass > 1 ? 1 : 0);

                const sDays = shuffleArray(days);
                for (const day of sDays) {
                  if (l.remaining <= 0) break;
                  
                  // Distribution constraint: try not to crowd same subject in one day unless necessary
                  if (getSubCount(day, l.classroom_id, l.subject_id) >= maxPerDay) continue;

                  for (let i = 0; i < normalPs.length; i++) {
                    if (l.remaining <= 0) break;
                    const p1 = normalPs[i].period_number;

                    if (allowed.length > 0 && !allowed.some(a => a.day == day && a.period == p1)) continue;
                    if (busyT.has(`${day}-${p1}-${l.teacher_id}`) || busyC.has(`${day}-${p1}-${l.classroom_id}`)) continue;

                    if (isDouble && l.remaining >= 2 && normalPs[i + 1]) {
                      const p2 = normalPs[i + 1].period_number;
                      if (p2 === p1 + 1 && !busyT.has(`${day}-${p2}-${l.teacher_id}`) && !busyC.has(`${day}-${p2}-${l.classroom_id}`)) {
                        if (allowed.length > 0 && !allowed.some(a => a.day == day && a.period == p2)) continue;

                        await db.run("INSERT INTO timetable (school_id, teacher_id, subject_id, classroom_id, room_id, day, period) VALUES (?, ?, ?, ?, ?, ?, ?)",
                          [school_id, l.teacher_id, l.subject_id, l.classroom_id, l.room_id, day, p1]);
                        await db.run("INSERT INTO timetable (school_id, teacher_id, subject_id, classroom_id, room_id, day, period) VALUES (?, ?, ?, ?, ?, ?, ?)",
                          [school_id, l.teacher_id, l.subject_id, l.classroom_id, l.room_id, day, p2]);
                        
                        busyT.add(`${day}-${p1}-${l.teacher_id}`); busyT.add(`${day}-${p2}-${l.teacher_id}`);
                        busyC.add(`${day}-${p1}-${l.classroom_id}`); busyC.add(`${day}-${p2}-${l.classroom_id}`);
                        incSubCount(day, l.classroom_id, l.subject_id);
                        l.remaining -= 2; assigned += 2;
                        break; // Move to next day
                      }
                    } else if (!isDouble || l.remaining === 1 || pass > 2) {
                      await db.run("INSERT INTO timetable (school_id, teacher_id, subject_id, classroom_id, room_id, day, period) VALUES (?, ?, ?, ?, ?, ?, ?)",
                        [school_id, l.teacher_id, l.subject_id, l.classroom_id, l.room_id, day, p1]);
                      busyT.add(`${day}-${p1}-${l.teacher_id}`);
                      busyC.add(`${day}-${p1}-${l.classroom_id}`);
                      incSubCount(day, l.classroom_id, l.subject_id);
                      l.remaining -= 1; assigned += 1;
                      break; // Move to next day
                    }
                  }
                }
              }
            }

            // 3. Final desperate fallback for anything left
            for (const l of loads) {
              if (l.remaining <= 0) continue;
              const sDays = shuffleArray(days);
              for (const day of sDays) {
                if (l.remaining <= 0) break;
                for (const p of normalPs) {
                  if (l.remaining <= 0) break;
                  if (busyT.has(`${day}-${p.period_number}-${l.teacher_id}`) || busyC.has(`${day}-${p.period_number}-${l.classroom_id}`)) continue;
                  
                  await db.run("INSERT INTO timetable (school_id, teacher_id, subject_id, classroom_id, room_id, day, period) VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [school_id, l.teacher_id, l.subject_id, l.classroom_id, l.room_id, day, p.period_number]);
                  busyT.add(`${day}-${p.period_number}-${l.teacher_id}`);
                  busyC.add(`${day}-${p.period_number}-${l.classroom_id}`);
                  l.remaining -= 1; assigned += 1;
                }
              }
            }

            res.json({ success: true, count: assigned });
          } catch (generateErr) {
            console.error("Auto Gen Error:", generateErr);
            res.status(500).json({ success: false, error: generateErr.message });
          }
          break;
        default:
          res.status(400).json({ error: 'Unknown action: ' + action });
      }
    } catch (e) {
      console.error(e);
      res.status(500).json({ error: e.message });
    }
  });

  // Proxy static PHP files as HTML for preview
  const servePhpAsHtml = (filePath: string, req: any, res: any) => {
    if (fs.existsSync(filePath)) {
        let content = fs.readFileSync(filePath, 'utf8');
        
        // Handle basic includes
        content = content.replace(/<\?php\s+require_once\s+'(.*?)';\s+.*?\?>/g, (match, includedFile) => {
            const fullPath = path.join(path.dirname(filePath), includedFile);
            if (fs.existsSync(fullPath)) {
                return fs.readFileSync(fullPath, 'utf8');
            }
            return `<!-- Include not found: ${includedFile} -->`;
        });

        // Simple Role Simulation (Targeting specific blocks)
        // Assume 'admin' role for preview unless we want to toggle
        const role = 'admin'; 
        
        // Handle simple if(hasRole(...))
        content = content.replace(/<\?php\s+if\s*\(hasRole\('(.*?)'\)\):\s+\?>(.*?)<\?php\s+else:\s+\?>(.*?)<\?php\s+endif;\s+\?>/gs, (match, targetRole, ifContent, elseContent) => {
            return role === targetRole ? ifContent : elseContent;
        });
        content = content.replace(/<\?php\s+if\s*\(hasRole\('(.*?)'\)\):\s+\?>(.*?)<\?php\s+endif;\s+\?>/gs, (match, targetRole, ifContent) => {
            return role === targetRole ? ifContent : '';
        });

        // Strip remaining PHP tags
        content = content.replace(/<\?php.*?\?>/gs, '');
        content = content.replace(/<\?=.*?\?>/gs, '');
        
        res.setHeader('Content-Type', 'text/html');
        res.send(content);
    } else {
        res.status(404).send('File not found.');
    }
  };

  app.get('/', (req, res) => servePhpAsHtml(path.join(process.cwd(), 'index.php'), req, res));
  app.get('/index.php', (req, res) => servePhpAsHtml(path.join(process.cwd(), 'index.php'), req, res));
  app.get('/dashboard.php', (req, res) => servePhpAsHtml(path.join(process.cwd(), 'dashboard.php'), req, res));
  app.get('/timetable.php', (req, res) => servePhpAsHtml(path.join(process.cwd(), 'timetable.php'), req, res));
  app.get('/register.php', (req, res) => servePhpAsHtml(path.join(process.cwd(), 'register.php'), req, res));

  app.post('/api/register', async (req, res) => {
    const { name, school_name, school_code, username, password } = req.body;
    try {
      await db.run("BEGIN TRANSACTION");
      
      let schoolId;
      const existingSchool = await db.get("SELECT id FROM schools WHERE code = ?", [school_code]);
      if (existingSchool) {
        schoolId = existingSchool.id;
      } else {
        const result = await db.run("INSERT INTO schools (name, code) VALUES (?, ?)", [school_name, school_code]);
        schoolId = result.lastID;
      }

      await db.run(
        "INSERT INTO users (username, password, name, role, school_id, is_approved) VALUES (?, ?, ?, ?, ?, ?)",
        [username, password, name, 'admin', schoolId, 0]
      );
      
      await db.run("COMMIT");
      res.json({ message: 'ลงทะเบียนสำเร็จ กรุณารอผู้อนุมัติ' });
    } catch (e: any) {
      await db.run("ROLLBACK");
      res.status(400).json({ error: 'ไม่สามารถลงทะเบียนได้ (อาจมีชื่อผู้ใช้นี้แล้ว)' });
    }
  });

  // Timetable APIs
  app.get('/api/timetable/:schoolId', async (req, res) => {
    const { schoolId } = req.params;
    const entries = await db.all(`
      SELECT t.*, s.name as subject_name, s.code as subject_code, te.name as teacher_name, r.name as room_name, c.name as class_name
      FROM timetable_entries t
      JOIN subjects s ON t.subject_id = s.id
      JOIN teachers te ON t.teacher_id = te.id
      JOIN rooms r ON t.room_id = r.id
      JOIN class_groups c ON t.class_group_id = c.id
      WHERE t.school_id = ?
    `, [schoolId]);
    res.json(entries);
  });

  // Admin APIs (Management)
  app.get('/api/admin/pending-users', async (req, res) => {
    const users = await db.all(`
      SELECT u.id, u.name, u.role, s.name as school_name 
      FROM users u 
      LEFT JOIN schools s ON u.school_id = s.id 
      WHERE u.is_approved = 0
    `);
    res.json(users);
  });

  app.post('/api/admin/approve-user', async (req, res) => {
    const { userId } = req.body;
    await db.run("UPDATE users SET is_approved = 1 WHERE id = ?", [userId]);
    res.json({ success: true });
  });

  // Database Update API (Automatic migrations could be added here)
  app.post('/api/admin/update-db', async (req, res) => {
    // This could run sql files or check schema
    res.json({ success: true, message: 'ฐานข้อมูลเป็นปัจจุบันแล้ว' });
  });

  if (process.env.NODE_ENV !== 'production') {
    const vite = await createViteServer({
      server: { middlewareMode: true },
      appType: 'spa',
    });
    app.use(vite.middlewares);
  } else {
    const distPath = path.join(process.cwd(), 'dist');
    app.use(express.static(distPath));
    app.get('*', (req, res) => {
      res.sendFile(path.join(distPath, 'index.html'));
    });
  }

  app.listen(PORT, '0.0.0.0', () => {
    console.log(`Server running on http://localhost:${PORT}`);
  });
}

startServer();

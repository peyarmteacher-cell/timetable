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
      school_id INTEGER,
      FOREIGN KEY(school_id) REFERENCES schools(id)
    );

    CREATE TABLE IF NOT EXISTS subjects (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      code TEXT NOT NULL,
      name TEXT NOT NULL,
      full_name TEXT,
      hours_per_week INTEGER DEFAULT 1,
      is_double INTEGER DEFAULT 0, -- 0: single, 1: double
      school_id INTEGER,
      FOREIGN KEY(school_id) REFERENCES schools(id)
    );

    CREATE TABLE IF NOT EXISTS rooms (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      type TEXT, -- regular, lab, etc.
      school_id INTEGER,
      FOREIGN KEY(school_id) REFERENCES schools(id)
    );

    CREATE TABLE IF NOT EXISTS class_groups (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL, -- e.g., ป.1/1
      level TEXT NOT NULL,
      school_id INTEGER,
      FOREIGN KEY(school_id) REFERENCES schools(id)
    );

    CREATE TABLE IF NOT EXISTS timetable_entries (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      subject_id INTEGER,
      teacher_id INTEGER,
      room_id INTEGER,
      class_group_id INTEGER,
      day_of_week INTEGER, -- 1-5 (Mon-Fri)
      period INTEGER, -- 1-8
      is_fixed INTEGER DEFAULT 0,
      school_id INTEGER,
      FOREIGN KEY(subject_id) REFERENCES subjects(id),
      FOREIGN KEY(teacher_id) REFERENCES teachers(id),
      FOREIGN KEY(room_id) REFERENCES rooms(id),
      FOREIGN KEY(class_group_id) REFERENCES class_groups(id)
    );
  `);

  // สร้าง Super Admin ถ้ายังไม่มี
  const superAdmin = await db.get("SELECT * FROM users WHERE role = 'super_admin'");
  if (!superAdmin) {
    await db.run(
      "INSERT INTO users (username, password, name, role, is_approved) VALUES (?, ?, ?, ?, ?)",
      ['admin', '123456', 'Super Admin', 'super_admin', 1]
    );
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

  // Proxy static PHP files as HTML for preview
  const servePhpAsHtml = (filePath, req, res) => {
    if (fs.existsSync(filePath)) {
        let content = fs.readFileSync(filePath, 'utf8');
        // Simple PHP tag stripping for preview
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

import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { 
  LayoutDashboard, 
  Calendar, 
  BookOpen, 
  GraduationCap, 
  Home, 
  Settings,
  LogOut,
  Plus,
  RefreshCw,
  MoreVertical
} from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { motion } from 'motion/react';

const DashboardPage: React.FC<{ user: any, onLogout: () => void }> = ({ user, onLogout }) => {
  const navigate = useNavigate();

  const stats = [
    { title: 'วิชาทั้งหมด', value: '12', icon: BookOpen, color: 'text-blue-600' },
    { title: 'ครูผู้สอน', value: '8', icon: GraduationCap, color: 'text-green-600' },
    { title: 'ห้องเรียน', value: '6', icon: Home, color: 'text-purple-600' },
    { title: 'คาบเรียน/สัปดาห์', value: '35', icon: Calendar, color: 'text-orange-600' },
  ];

  return (
    <div className="flex h-screen bg-slate-50">
      {/* Sidebar */}
      <div className="w-64 bg-white border-r flex flex-col">
        <div className="p-6 border-b">
          <h1 className="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
            SchoolTimetable
          </h1>
          <p className="text-[10px] uppercase tracking-widest text-muted-foreground font-semibold">Pro Version</p>
        </div>
        <nav className="flex-1 p-4 space-y-1">
          <Button variant="ghost" className="w-full justify-start gap-3 bg-slate-100/80 text-primary">
            <LayoutDashboard size={18} />
            แดชบอร์ด
          </Button>
          <Button variant="ghost" className="w-full justify-start gap-3 text-muted-foreground hover:text-primary" onClick={() => navigate('/timetable')}>
            <Calendar size={18} />
            จัดการตารางสอน
          </Button>
          <Button variant="ghost" className="w-full justify-start gap-3 text-muted-foreground hover:text-primary">
            <BookOpen size={18} />
            ข้อมูลรายวิชา
          </Button>
          <Button variant="ghost" className="w-full justify-start gap-3 text-muted-foreground hover:text-primary">
            <GraduationCap size={18} />
            ข้อมูลครู
          </Button>
          <Button variant="ghost" className="w-full justify-start gap-3 text-muted-foreground hover:text-primary" onClick={() => navigate('/settings')}>
            <Settings size={18} />
            ตั้งค่าโรงเรียน
          </Button>
        </nav>
        <div className="p-4 border-t space-y-4">
          <div className="flex items-center gap-3 px-2 py-3 bg-slate-50 rounded-lg">
            <div className="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold">
              {user.name.charAt(0)}
            </div>
            <div className="flex-1 overflow-hidden">
              <p className="text-sm font-medium truncate">{user.name}</p>
              <p className="text-[10px] text-muted-foreground uppercase">{user.role}</p>
            </div>
          </div>
          <Button variant="ghost" className="w-full justify-start gap-3 text-destructive hover:bg-destructive/10" onClick={onLogout}>
            <LogOut size={18} />
            ออกจากระบบ
          </Button>
        </div>
      </div>

      {/* Main Content */}
      <div className="flex-1 overflow-auto">
        <header className="h-16 border-b bg-white/80 backdrop-blur-md flex items-center justify-between px-8 sticky top-0 z-10">
          <h2 className="font-semibold text-lg">ภาพรวมระบบ</h2>
          <div className="flex items-center gap-4">
            <Button size="sm" className="gap-2 bg-blue-600 hover:bg-blue-700" onClick={() => navigate('/timetable')}>
              <Plus size={16} /> จัดตารางด่วน
            </Button>
          </div>
        </header>

        <main className="p-8 space-y-8">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {stats.map((stat, i) => (
              <motion.div
                key={stat.title}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: i * 0.1 }}
              >
                <Card className="border-none shadow-sm h-full hover:shadow-md transition-shadow">
                  <CardHeader className="flex flex-row items-center justify-between pb-2">
                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{stat.title}</CardTitle>
                    <stat.icon size={16} className={stat.color} />
                  </CardHeader>
                  <CardContent>
                    <p className="text-3xl font-bold">{stat.value}</p>
                  </CardContent>
                </Card>
              </motion.div>
            ))}
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <Card className="lg:col-span-2 border-none shadow-sm">
              <CardHeader className="flex flex-row items-center justify-between">
                <div>
                  <CardTitle>ตารางสอนล่าสุด</CardTitle>
                  <CardDescription>ความเคลื่อนไหวล่าสุดของตารางแต่ละชั้นเรียน</CardDescription>
                </div>
                <Button variant="outline" size="sm" className="gap-2">
                  <RefreshCw size={14} /> รีเฟรช
                </Button>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {[1, 2, 3].map((item) => (
                    <div key={item} className="flex items-center justify-between p-4 rounded-xl border border-dotted border-slate-200 bg-white hover:border-blue-200 transition-colors">
                      <div className="flex items-center gap-4">
                        <div className="w-10 h-10 rounded-lg bg-slate-50 flex items-center justify-center font-bold text-slate-600">
                          {item}
                        </div>
                        <div>
                          <p className="font-semibold">ตารางเรียน ป.1/{item}</p>
                          <p className="text-xs text-muted-foreground">อัปเดตล่าสุด: เมื่อ 2 ชั่วโมงที่แล้ว</p>
                        </div>
                      </div>
                      <Button variant="ghost" size="icon">
                        <MoreVertical size={16} />
                      </Button>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>

            <Card className="border-none shadow-sm bg-gradient-to-br from-blue-600 to-indigo-700 text-white">
              <CardHeader>
                <CardTitle>สถานะความพร้อม</CardTitle>
                <CardDescription className="text-blue-100">ตรวจสอบความถูกต้องก่อนเปิดเทอม</CardDescription>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="space-y-2">
                  <div className="flex justify-between text-sm">
                    <span>ตารางสอนที่เสร็จสมบูรณ์</span>
                    <span>85%</span>
                  </div>
                  <div className="h-2 bg-white/20 rounded-full overflow-hidden">
                    <div className="h-full bg-white w-[85%]"></div>
                  </div>
                </div>
                
                <div className="bg-white/10 p-4 rounded-xl space-y-3">
                  <p className="text-sm font-medium">สิ่งที่ต้องทำ:</p>
                  <ul className="text-xs space-y-2 text-blue-50">
                    <li className="flex items-start gap-2">
                      <span className="mt-1 block w-1 h-1 rounded-full bg-white"></span>
                      ตรวจสอบรายวิชาภาษาไทย ป.3 ยังมีคาบว่าง
                    </li>
                    <li className="flex items-start gap-2">
                      <span className="mt-1 block w-1 h-1 rounded-full bg-white"></span>
                      ห้องแล็บคอมพิวเตอร์มีการใช้ชนกันช่วงเช้าวันพุธ
                    </li>
                  </ul>
                </div>
              </CardContent>
            </Card>
          </div>
        </main>
      </div>
    </div>
  );
};

export default DashboardPage;

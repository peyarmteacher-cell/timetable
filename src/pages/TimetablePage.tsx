import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { 
  ChevronLeft, 
  ChevronRight, 
  Download, 
  Wand2, 
  Settings2, 
  LayoutGrid,
  Maximize2,
  Minimize2,
  Lock,
  Unlock,
  Trash2,
  Users
} from 'lucide-react';
import { motion, Reorder } from 'motion/react';
import { toast } from 'sonner';

type Period = {
  id: string;
  subjectCode: string;
  subjectName: string;
  teacher: string;
  room: string;
  isDouble: boolean;
  isFixed?: boolean;
};

type DaySchedule = (Period | null)[];

const DAYS = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์'];
const PERIOD_TIMES = [
  '08:30', '09:20', '10:10', '11:00', '12:00', '13:00', '13:50', '14:40', '15:30'
];

const TimetablePage: React.FC<{ user: any }> = ({ user }) => {
  const [selectedClass, setSelectedClass] = useState('ป.1/1');
  const [schedule, setSchedule] = useState<DaySchedule[]>(Array(5).fill(null).map(() => Array(8).fill(null)));
  const [availableSlots, setAvailableSlots] = useState({ rooms: 15, teachers: 12 });
  const [isEditing, setIsEditing] = useState(false);

  // ข้อมูลจำลองสำหรับทดสอบ
  const mockSubjects = [
    { code: 'ท11101', name: 'ภาษาไทย', teacher: 'ครูสมศรี', room: '101', hours: 5, isDouble: true },
    { code: 'ค11101', name: 'คณิตศาสตร์', teacher: 'ครูสมชาย', room: '102', hours: 4, isDouble: false },
    { code: 'อ11101', name: 'ภาษาอังกฤษ', teacher: 'ครูสมหญิง', room: '103', hours: 3, isDouble: false },
    { code: 'ส11101', name: 'สังคมศึกษา', teacher: 'ครูบุญมา', room: '104', hours: 2, isDouble: false },
    { code: 'ว11101', name: 'วิทยาศาสตร์', teacher: 'ครูมานะ', room: 'แล็บ 1', hours: 2, isDouble: true },
  ];

  const handleAutoGenerate = () => {
    toast.promise(new Promise((resolve) => setTimeout(resolve, 1500)), {
      loading: 'กำลังวิเคราะห์หาช่องว่างและจัดตารางอัตโนมัติ...',
      success: 'จัดตารางเสร็จสมบูรณ์! พบช่องว่างห้องเรียนเฉลี่ย 8 ห้องต่อคาบ',
      error: 'ไม่สามารถจัดตารางได้'
    });

    const newSchedule = Array(5).fill(null).map(() => Array(8).fill(null));
    
    // อัลกอริทึมจำลองการจัดตาราง
    DAYS.forEach((_, dayIdx) => {
      // ลงวิชาภาษาไทย (คาบคู่) สัปดาห์ละ 2-3 ครั้ง
      if (dayIdx % 2 === 0) {
        newSchedule[dayIdx][0] = { ...mockSubjects[0], id: `mon-th-1` };
        newSchedule[dayIdx][1] = { ...mockSubjects[0], id: `mon-th-2` };
      }
      
      // ลงวิชาคณิตศาสตร์
      newSchedule[dayIdx][2] = { ...mockSubjects[1], id: `math-${dayIdx}` };
    });

    setSchedule(newSchedule);
  };

  const handleDragEnd = (dayIdx: number, newDaySchedule: DaySchedule) => {
    const newSchedule = [...schedule];
    newSchedule[dayIdx] = newDaySchedule;
    setSchedule(newSchedule);
  };

  const toggleFix = (dayIdx: number, periodIdx: number) => {
    const newSchedule = [...schedule];
    const period = newSchedule[dayIdx][periodIdx];
    if (period) {
      period.isFixed = !period.isFixed;
      setSchedule(newSchedule);
      toast.info(period.isFixed ? 'ล็อคคาบเรียนแล้ว' : 'ปลดล็อคคาบเรียนแล้ว');
    }
  };

  return (
    <div className="p-8 space-y-6 bg-slate-50 min-h-screen">
      <div className="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border">
        <div>
          <div className="flex items-center gap-3 mb-1">
            <Button variant="ghost" size="icon" onClick={() => window.history.back()}>
              <ChevronLeft size={20} />
            </Button>
            <h1 className="text-2xl font-bold text-slate-900 leading-none">จัดการตารางสอน</h1>
          </div>
          <p className="text-muted-foreground ml-11 text-sm">ออกแบบและจัดสรรเวลาเรียนของนักเรียนชั้น {selectedClass}</p>
        </div>
        
        <div className="flex items-center gap-2">
          <div className="flex items-center gap-4 mr-6 pr-6 border-r">
            <div className="text-right">
              <p className="text-xs text-muted-foreground uppercase font-bold tracking-tighter">ห้องว่าง</p>
              <p className="text-xl font-bold text-green-600">{availableSlots.rooms}</p>
            </div>
            <div className="text-right">
              <p className="text-xs text-muted-foreground uppercase font-bold tracking-tighter">ครูว่าง</p>
              <p className="text-xl font-bold text-blue-600">{availableSlots.teachers}</p>
            </div>
          </div>
          
          <Button variant="outline" className="gap-2" onClick={handleAutoGenerate}>
            <Wand2 size={16} className="text-blue-600" />
            จัดตารางอัตโนมัติ
          </Button>
          <Button variant="outline" size="icon">
            <Download size={16} />
          </Button>
          <Button onClick={() => setIsEditing(!isEditing)} variant={isEditing ? 'default' : 'secondary'}>
            {isEditing ? 'บันทึกตาราง' : 'แก้ไขตาราง'}
          </Button>
        </div>
      </div>

      <div className="grid grid-cols-12 gap-8">
        {/* Main Timetable */}
        <div className="col-span-9">
          <Card className="border-none shadow-sm overflow-hidden">
            <div className="bg-white border-b overflow-x-auto">
              <div className="min-w-[1000px]">
                {/* Header: Periods */}
                <div className="grid grid-cols-[100px_repeat(8,1fr)] border-b bg-slate-50/50">
                  <div className="p-4 border-r flex items-center justify-center font-bold text-xs text-slate-400">วัน / คาบ</div>
                  {PERIOD_TIMES.slice(0, 8).map((time, i) => (
                    <div key={i} className="p-4 text-center border-r last:border-r-0">
                      <p className="text-xs font-bold text-slate-400 uppercase leading-none mb-1">คาบ {i + 1}</p>
                      <p className="text-sm font-semibold text-slate-900">{time}</p>
                    </div>
                  ))}
                </div>

                {/* Days and Schedule */}
                <div className="flex flex-col">
                  {DAYS.map((day, dayIdx) => (
                    <div key={day} className="grid grid-cols-[100px_1fr] border-b last:border-b-0">
                      <div className="bg-slate-50/30 border-r flex items-center justify-center font-bold text-slate-700">
                        {day}
                      </div>
                      
                      <Reorder.Group 
                        axis="x" 
                        values={schedule[dayIdx]} 
                        onReorder={(val) => handleDragEnd(dayIdx, val)}
                        className="grid grid-cols-8 divide-x"
                      >
                        {schedule[dayIdx].map((item, periodIdx) => (
                          <Reorder.Item 
                            key={item ? item.id : `empty-${dayIdx}-${periodIdx}`} 
                            value={item}
                            drag={isEditing && !!item && !item.isFixed}
                            className={`p-1 h-32 flex flex-col group ${isEditing && !item?.isFixed ? 'cursor-grab active:cursor-grabbing' : ''}`}
                          >
                            {item ? (
                              <motion.div
                                layout
                                className={`flex-1 rounded-xl p-3 flex flex-col justify-between transition-all ${
                                  item.isFixed ? 'bg-slate-100 border-slate-200' : 'bg-blue-50 border-blue-100'
                                } border relative shadow-sm group-hover:shadow-md h-full`}
                              >
                                <div className="flex justify-between items-start">
                                  <span className="text-[10px] font-bold bg-blue-600 text-white px-1.5 py-0.5 rounded uppercase">{item.subjectCode}</span>
                                  {isEditing && (
                                    <div className="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                      <button onClick={() => toggleFix(dayIdx, periodIdx)} className="p-1 hover:bg-white rounded text-slate-400 hover:text-blue-600 transition-colors">
                                        {item.isFixed ? <Lock size={12} /> : <Unlock size={12} />}
                                      </button>
                                      <button className="p-1 hover:bg-white rounded text-slate-400 hover:text-red-500 transition-colors">
                                        <Trash2 size={12} />
                                      </button>
                                    </div>
                                  )}
                                </div>
                                <div className="flex-1 py-1 flex flex-col justify-center">
                                  <p className="text-xs font-bold text-slate-800 leading-tight line-clamp-2">{item.subjectName}</p>
                                  <p className="text-[10px] text-muted-foreground mt-0.5 flex items-center gap-1">
                                    <Users size={10} /> {item.teacher}
                                  </p>
                                </div>
                                <div className="flex justify-between items-center pt-1 border-t border-blue-200/50">
                                  <span className="text-[9px] font-semibold text-blue-700/70">{item.room}</span>
                                  {item.isDouble && <span className="text-[9px] px-1 bg-white rounded-full border border-blue-200 text-blue-600 font-bold">คู่</span>}
                                </div>
                              </motion.div>
                            ) : (
                              <div className="flex-1 rounded-xl bg-slate-50/50 border border-dashed border-slate-200 hover:border-blue-200 transition-colors flex items-center justify-center">
                                {isEditing && <Plus size={16} className="text-slate-300" />}
                              </div>
                            )}
                          </Reorder.Item>
                        ))}
                      </Reorder.Group>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </Card>
        </div>

        {/* Sidebar Controls */}
        <div className="col-span-3 space-y-6">
          <Card className="border-none shadow-sm">
            <CardHeader className="pb-3">
              <CardTitle className="text-sm">ตารางชั้นเรียน</CardTitle>
            </CardHeader>
            <CardContent className="space-y-2">
              {['ป.1/1', 'ป.1/2', 'ป.2/1', 'ป.2/2', 'ป.3/1'].map((cls) => (
                <Button 
                  key={cls}
                  variant={selectedClass === cls ? 'default' : 'ghost'} 
                  className="w-full justify-start font-medium h-10 px-4"
                  onClick={() => setSelectedClass(cls)}
                >
                  <LayoutGrid size={16} className="mr-3" />
                  ชั้นเรียน {cls}
                </Button>
              ))}
            </CardContent>
          </Card>

          <Card className="border-none shadow-sm bg-indigo-600 text-white">
            <CardHeader>
              <CardTitle className="text-sm flex items-center gap-2">
                <Settings2 size={16} /> บันทึกช่วยจำ
              </CardTitle>
            </CardHeader>
            <CardContent className="text-xs space-y-4">
              <p className="opacity-90">คาบชุมนุมและลูกเสือ (บ่ายวันศุกร์) จะต้องเรียนรวมกันทุกห้อง</p>
              <div className="bg-black/10 rounded-lg p-3 space-y-2">
                <div className="flex justify-between items-center">
                  <span>สถานะความสมบูรณ์</span>
                  <span className="font-bold">45%</span>
                </div>
                <div className="h-1.5 bg-black/20 rounded-full overflow-hidden">
                  <div className="h-full bg-white w-[45%]"></div>
                </div>
              </div>
              <Button variant="outline" className="w-full bg-white/10 border-white/20 hover:bg-white/20 text-white h-9">
                ดูวิชารอจัดตาราง
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
};

export default TimetablePage;

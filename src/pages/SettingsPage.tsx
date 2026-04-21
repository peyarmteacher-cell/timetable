import React from 'react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ChevronLeft, Save } from 'lucide-react';
import { toast } from 'sonner';

const SettingsPage: React.FC<{ user: any }> = ({ user }) => {
  const handleSave = () => {
    toast.success('บันทึกการตั้งค่าโรงเรียนเรียบร้อยแล้ว');
  };

  return (
    <div className="p-8 max-w-4xl mx-auto space-y-8">
      <div className="flex items-center gap-4">
        <Button variant="ghost" size="icon" onClick={() => window.history.back()}>
          <ChevronLeft size={20} />
        </Button>
        <h1 className="text-3xl font-bold tracking-tight">ตั้งค่าโรงเรียน</h1>
      </div>

      <div className="grid gap-8">
        <Card>
          <CardHeader>
            <CardTitle>ข้อมูลทั่วไป</CardTitle>
            <CardDescription>จัดการข้อมูลพื้นฐานของโรงเรียน</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>ชื่อโรงเรียน</Label>
                <Input defaultValue="โรงเรียนบ้านหนองบัว" />
              </div>
              <div className="space-y-2">
                <Label>รหัสโรงเรียน</Label>
                <Input defaultValue="10310001" disabled />
              </div>
            </div>
            <div className="space-y-2">
              <Label>สังกัด</Label>
              <Input defaultValue="สพป.บุรีรัมย์ เขต 3" />
            </div>
            <Button onClick={handleSave}>บันทึกข้อมูล</Button>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>โครงสร้างเวลาเรียน</CardTitle>
            <CardDescription>กำหนดคาบเรียนในแต่ละวัน</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>จำนวนคาบต่อวัน</Label>
                <Input type="number" defaultValue="8" />
              </div>
              <div className="space-y-2">
                <Label>ระยะเวลาต่อคาบ (นาที)</Label>
                <Input type="number" defaultValue="50" />
              </div>
            </div>
            <Button onClick={handleSave}>บันทึกโครงสร้างเวลา</Button>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default SettingsPage;

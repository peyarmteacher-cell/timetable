import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { fetchApi } from '@/lib/api';
import { toast } from 'sonner';
import { motion } from 'motion/react';

const RegisterPage: React.FC = () => {
  const [formData, setFormData] = useState({
    name: '',
    school_name: '',
    school_code: '',
    username: '',
    password: '',
    confirmPassword: ''
  });
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const handleRegister = async (e: React.FormEvent) => {
    e.preventDefault();
    if (formData.password !== formData.confirmPassword) {
      toast.error('รหัสผ่านไม่ตรงกัน');
      return;
    }
    
    setLoading(true);
    try {
      await fetchApi('/api/register', {
        method: 'POST',
        body: JSON.stringify(formData),
      });
      toast.success('ลงทะเบียนสำเร็จ กรุณารอการอนุมัติจาก Super Admin');
      navigate('/login');
    } catch (error: any) {
      toast.error(error.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex min-h-screen items-center justify-center p-4">
      <motion.div
        initial={{ opacity: 0, scale: 0.95 }}
        animate={{ opacity: 1, scale: 1 }}
        transition={{ duration: 0.3 }}
      >
        <Card className="w-full max-w-lg">
          <CardHeader className="space-y-1">
            <CardTitle className="text-2xl font-bold text-center">ลงทะเบียนโรงเรียนใหม่</CardTitle>
            <CardDescription className="text-center">
              กรอกข้อมูลเพื่อขอใช้งานระบบ School Timetable Pro
            </CardDescription>
          </CardHeader>
          <form onSubmit={handleRegister}>
            <CardContent className="grid gap-4 sm:grid-cols-2">
              <div className="space-y-2">
                <Label htmlFor="name">ชื่อผู้ติดต่อ</Label>
                <Input 
                  id="name" 
                  value={formData.name}
                  onChange={(e) => setFormData({...formData, name: e.target.value})}
                  required 
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="school_name">ชื่อโรงเรียน</Label>
                <Input 
                  id="school_name" 
                  value={formData.school_name}
                  onChange={(e) => setFormData({...formData, school_name: e.target.value})}
                  required 
                />
              </div>
              <div className="space-y-2 sm:col-span-2">
                <Label htmlFor="school_code">รหัสโรงเรียน 10 หลัก (หรือรหัสอ้างอิง)</Label>
                <Input 
                  id="school_code" 
                  value={formData.school_code}
                  onChange={(e) => setFormData({...formData, school_code: e.target.value})}
                  required 
                />
              </div>
              <div className="space-y-2 sm:col-span-2">
                <Label htmlFor="username">ชื่อผู้ใช้ (ที่จะใช้เข้าสู่ระบบ)</Label>
                <Input 
                  id="username" 
                  value={formData.username}
                  onChange={(e) => setFormData({...formData, username: e.target.value})}
                  required 
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="password">รหัสผ่าน</Label>
                <Input 
                  id="password" 
                  type="password"
                  value={formData.password}
                  onChange={(e) => setFormData({...formData, password: e.target.value})}
                  required 
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="confirmPassword">ยืนยันรหัสผ่าน</Label>
                <Input 
                  id="confirmPassword" 
                  type="password"
                  value={formData.confirmPassword}
                  onChange={(e) => setFormData({...formData, confirmPassword: e.target.value})}
                  required 
                />
              </div>
            </CardContent>
            <CardFooter className="flex flex-col gap-4">
              <Button type="submit" className="w-full" disabled={loading}>
                {loading ? 'กำลังประมวลผล...' : 'ลงทะเบียนขอใช้งาน'}
              </Button>
              <div className="text-sm text-center text-muted-foreground">
                มีบัญชีอยู่แล้ว? <Link to="/login" className="text-primary hover:underline">เข้าสู่ระบบ</Link>
              </div>
            </CardFooter>
          </form>
        </Card>
      </motion.div>
    </div>
  );
};

export default RegisterPage;

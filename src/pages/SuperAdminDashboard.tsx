import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { fetchApi } from '@/lib/api';
import { toast } from 'sonner';
import { LayoutDashboard, Users, LogOut, CheckCircle, XCircle } from 'lucide-react';

const SuperAdminDashboard: React.FC<{ user: any, onLogout: () => void }> = ({ user, onLogout }) => {
  const [pendingUsers, setPendingUsers] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadPendingUsers();
  }, []);

  const loadPendingUsers = async () => {
    try {
      const data = await fetchApi('/api/admin/pending-users');
      setPendingUsers(data);
    } catch (error: any) {
      toast.error('ไม่สามารถโหลดข้อมูลผู้ใช้ที่รออนุมัติได้');
    } finally {
      setLoading(false);
    }
  };

  const handleApprove = async (userId: number) => {
    try {
      await fetchApi('/api/admin/approve-user', {
        method: 'POST',
        body: JSON.stringify({ userId }),
      });
      toast.success('อนุมัติผู้ใช้งานเรียบร้อยแล้ว');
      loadPendingUsers();
    } catch (error: any) {
      toast.error('ไม่สามารถอนุมัติผู้ใช้งานได้');
    }
  };

  return (
    <div className="flex h-screen bg-slate-100">
      {/* Sidebar */}
      <div className="w-64 bg-white border-r flex flex-col">
        <div className="p-6 border-bottom">
          <h1 className="text-xl font-bold text-primary">Super Admin</h1>
          <p className="text-xs text-muted-foreground">ระบบจัดการหลัก</p>
        </div>
        <nav className="flex-1 p-4 space-y-2">
          <Button variant="ghost" className="w-full justify-start gap-2 bg-slate-100">
            <LayoutDashboard size={18} />
            หน้าแรก
          </Button>
          <Button variant="ghost" className="w-full justify-start gap-2">
            <Users size={18} />
            จัดการโรงเรียน
          </Button>
        </nav>
        <div className="p-4 border-t">
          <Button variant="ghost" className="w-full justify-start gap-2 text-destructive" onClick={onLogout}>
            <LogOut size={18} />
            ออกจากระบบ
          </Button>
        </div>
      </div>

      {/* Main Content */}
      <div className="flex-1 overflow-auto p-8">
        <div className="max-w-4xl mx-auto space-y-8">
          <div className="flex justify-between items-center text-slate-900">
            <div>
              <h2 className="text-3xl font-bold tracking-tight">ยินดีต้อนรับ, {user.name}</h2>
              <p className="text-muted-foreground">ตรวจสอบและอนุมัติการขอใช้งานระบบจากโรงเรียนต่างๆ</p>
            </div>
            <Button onClick={() => fetchApi('/api/admin/update-db', { method: 'POST' })}>
              อัปเดตฐานข้อมูลอัตโนมัติ
            </Button>
          </div>

          <Card>
            <CardHeader>
              <CardTitle>รายการรออนุมัติ ({pendingUsers.length})</CardTitle>
              <CardDescription>โรงเรียนที่ขอสมัครใช้งานใหม่</CardDescription>
            </CardHeader>
            <CardContent>
              {loading ? (
                <div className="text-center py-8">กำลังโหลด...</div>
              ) : pendingUsers.length === 0 ? (
                <div className="text-center py-8 text-muted-foreground">ไม่มีรายการรออนุมัติ</div>
              ) : (
                <div className="space-y-4">
                  {pendingUsers.map((u: any) => (
                    <div key={u.id} className="flex items-center justify-between p-4 border rounded-lg bg-white">
                      <div>
                        <p className="font-semibold">{u.school_name}</p>
                        <p className="text-sm text-muted-foreground">ผู้ติดต่อ: {u.name} (ID: {u.username})</p>
                      </div>
                      <div className="flex gap-2">
                        <Button size="sm" className="gap-1" onClick={() => handleApprove(u.id)}>
                          <CheckCircle size={16} /> อนุมัติ
                        </Button>
                        <Button size="sm" variant="outline" className="gap-1 text-destructive">
                          <XCircle size={16} /> ปฏิเสธ
                        </Button>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
};

export default SuperAdminDashboard;

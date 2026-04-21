export const fetchApi = async (endpoint: string, options: any = {}) => {
  const response = await fetch(endpoint, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      ...options.headers,
    },
  });
  
  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.error || 'เกิดข้อผิดพลาดในการเชื่อมต่อ');
  }
  
  return response.json();
};

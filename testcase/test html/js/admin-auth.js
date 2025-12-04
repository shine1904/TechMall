// Admin Authentication Guard
// Kiểm tra quyền admin trước khi cho phép truy cập trang admin

class AdminAuth {
    static async checkAdminAccess() {
        const token = localStorage.getItem('token');
        const user = AdminAuth.getStoredUser();
        
        // Nếu không có token, chuyển về login
        if (!token) {
            AdminAuth.redirectToLogin('Vui lòng đăng nhập để truy cập trang quản trị');
            return false;
        }
        
        // Kiểm tra roles từ localStorage trước
        if (user && user.roles) {
            const hasAdminRole = user.roles.some(role => 
                role === 'super_admin' || 
                role === 'product_admin' || 
                role === 'order_admin' || 
                role === 'content_admin' || 
                role === 'report_admin'
            );
            
            if (!hasAdminRole) {
                AdminAuth.redirectToLogin('Bạn không có quyền truy cập trang quản trị');
                return false;
            }
        }
        
        // Verify với server
        try {
            const response = await AdminAuth.verifyAdminWithServer(token);
            if (!response.success) {
                AdminAuth.redirectToLogin('Token không hợp lệ hoặc không có quyền admin');
                return false;
            }
            return true;
        } catch (error) {
            console.error('Admin verification failed:', error);
            AdminAuth.redirectToLogin('Không thể xác thực quyền admin');
            return false;
        }
    }
    
    static async verifyAdminWithServer(token) {
        const API_BASE = await AdminAuth.resolveApiBase();
        
        const response = await fetch(`${API_BASE}/admin/verify`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        return await response.json();
    }
    
    static getStoredUser() {
        try {
            return JSON.parse(localStorage.getItem('user') || 'null');
        } catch {
            return null;
        }
    }
    
    static redirectToLogin(message = 'Vui lòng đăng nhập') {
        // Lưu thông báo lỗi
        sessionStorage.setItem('authError', message);
        
        // Xóa token và user data
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        
        // Redirect về login
        window.location.href = '/ecommerce_api/fe/login.html';
    }
    
    // Utility function để resolve API base
    static async resolveApiBase() {
        const candidates = [
            `${location.protocol}//localhost/ecommerce_api/index.php/api`,
            `${location.protocol}//127.0.0.1/ecommerce_api/index.php/api`,
            `${location.protocol}//${location.hostname}/ecommerce_api/index.php/api`,
        ];
        
        for (const base of candidates) {
            try {
                const controller = new AbortController();
                const timeout = setTimeout(() => controller.abort(), 1500);
                
                const response = await fetch(base.replace(/\/api$/, '') + '/health', {
                    signal: controller.signal
                });
                
                clearTimeout(timeout);
                
                if (response.ok) {
                    return base;
                }
            } catch (error) {
                // Try next candidate
            }
        }
        
        // Fallback
        return `${location.protocol}//localhost/ecommerce_api/index.php/api`;
    }
    
    // Kiểm tra quyền truy cập từng trang cụ thể
    static checkPageAccess(pageName) {
        const user = AdminAuth.getStoredUser();
        if (!user || !user.roles) return false;
        
        const userRoles = user.roles;
        
        // Super admin có quyền truy cập tất cả
        if (userRoles.includes('super_admin')) {
            return true;
        }
        
        // Định nghĩa quyền truy cập từng trang
        const pagePermissions = {
            'admin-products': ['super_admin', 'product_admin'],
            'admin-users': ['super_admin'], // Chỉ super_admin
            'admin-orders': ['super_admin', 'order_admin'] // Không có product_admin
        };
        
        const allowedRoles = pagePermissions[pageName] || [];
        return userRoles.some(role => allowedRoles.includes(role));
    }
    
    // Kiểm tra quyền specific cho từng trang (backward compatibility)
    static checkSpecificPermission(requiredRole = null) {
        const user = AdminAuth.getStoredUser();
        if (!user || !user.roles) return false;
        
        if (requiredRole) {
            return user.roles.includes(requiredRole);
        }
        
        // Nếu không chỉ định role cụ thể, chỉ cần là admin
        return user.roles.some(role => 
            role === 'super_admin' || 
            role.includes('admin')
        );
    }
    
}

// Auto-run admin check khi trang load
document.addEventListener('DOMContentLoaded', async () => {
    // Kiểm tra xem có phải trang admin không
    const isAdminPage = location.pathname.includes('admin-') || 
                       location.href.includes('admin-');
    
    if (isAdminPage) {
        // Xác định trang hiện tại
        let currentPage = '';
        if (location.pathname.includes('admin-products')) {
            currentPage = 'admin-products';
        } else if (location.pathname.includes('admin-users')) {
            currentPage = 'admin-users';
        } else if (location.pathname.includes('admin-orders')) {
            currentPage = 'admin-orders';
        }
        
        // Kiểm tra quyền truy cập tổng quát
        const hasGeneralAccess = await AdminAuth.checkAdminAccess();
        
        if (!hasGeneralAccess) {
            // Đã redirect trong checkAdminAccess
            return;
        }
        
        // Kiểm tra quyền truy cập trang cụ thể
        if (currentPage && !AdminAuth.checkPageAccess(currentPage)) {
            AdminAuth.redirectToLogin(`Bạn không có quyền truy cập trang ${currentPage.replace('admin-', 'quản lý ')}`);
            return;
        }
        
        console.log(`✅ Admin access verified for page: ${currentPage || 'unknown'}`);
    }
});

// Export for use in other files
window.AdminAuth = AdminAuth;
// Admin Products JavaScript
// API base (auto-detect localhost Apache, avoid dev server origin)
let API_BASE = null;

function candidateBases(){
  const prot = location.protocol.startsWith('http') ? location.protocol : 'http:';
  const host = location.hostname || 'localhost';
  const candidates = [
    `${prot}//localhost/ecommerce_api/index.php/api`,
    `${prot}//127.0.0.1/ecommerce_api/index.php/api`,
    `${prot}//${host}/ecommerce_api/index.php/api`,
  ];
  return [...new Set(candidates)];
}

async function resolveApiBase(){
  const cands = candidateBases();
  for(const base of cands){
    try{
      const ctrl = new AbortController();
      const t = setTimeout(()=>ctrl.abort(), 1500);
      const res = await fetch(base.replace(/\/api$/, '') + '/health', { signal: ctrl.signal });
      clearTimeout(t);
      const ctype = res.headers.get('content-type')||'';
      const txtRaw = await res.text(); const txt = (txtRaw||'').replace(/^\uFEFF/, '').trim();
      let json=null; try{ json = JSON.parse(txt);}catch{}
      if(res.ok && (json && (json.success===true || json.data || json.php || json.version) || ctype.includes('application/json'))){
        API_BASE = base;
        return base;
      }
    }catch(e){ /* try next */ }
  }
  API_BASE = `${location.protocol.startsWith('http')?location.protocol:'http:'}//localhost/ecommerce_api/index.php/api`;
  return API_BASE;
}

// Auth and state
const token = localStorage.getItem('token');
const user = (()=>{ try{return JSON.parse(localStorage.getItem('user')||'null')}catch{return null} })();
const canWrite = !!token; // writes require login; RBAC enforced on server
let currentPage = 1;
const pageSize = 10;
let lastCount = 0;
let totalPages = 1;
let pagination = null;

// Utils
function authHeaders(){ 
  const h={'Content-Type':'application/json','Accept':'application/json'}; 
  if(token) h['Authorization']='Bearer '+token; 
  return h; 
}

function money(n){ 
  return Number(n||0).toLocaleString('vi-VN') + '₫'; 
}

function getDefaultImage(){
  try{ 
    const origin = API_BASE ? new URL(API_BASE).origin : `${location.protocol.startsWith('http')?location.protocol:'http:'}//localhost`; 
    return origin + '/ecommerce_api/public/img/samsung-galaxy-23-ultra-tim.jpg.webp'; 
  }catch{ 
    return 'http://localhost/ecommerce_api/public/img/samsung-galaxy-23-ultra-tim.jpg.webp'; 
  }
}

function normalizeThumbList(raw){
  if(!raw) return [];
  if(Array.isArray(raw)) return raw.filter(Boolean);
  if(typeof raw === 'string'){
    const s = raw.trim();
    try{ const j = JSON.parse(s); if(Array.isArray(j)) return j.filter(Boolean);}catch{}
    return s.split(',').map(x=>x.trim()).filter(Boolean);
  }
  return [];
}

function showMessage(msg, isError=false){
  const style = isError ? 'background:#dc3545;color:white' : 'background:#28a745;color:white';
  const div = document.createElement('div');
  div.style.cssText = `position:fixed;top:20px;right:20px;padding:12px 16px;border-radius:6px;z-index:9999;${style}`;
  div.textContent = msg; 
  document.body.appendChild(div); 
  setTimeout(()=>div.remove(), 2800);
}

// Load categories from Categories API
async function loadCategories(){
  const sel = document.getElementById('selCategory');
  const selForm = document.getElementById('prdCategory');
  sel.innerHTML = '<option value="">Tất cả danh mục</option>';
  selForm.innerHTML = '<option value="">Chọn danh mục</option>';
  
  try{
    // Gọi API categories trực tiếp
    const res = await fetch(API_BASE + '/categories');
    const status = res.status; 
    const ctype = res.headers.get('content-type')||'';
    const txtRaw = await res.text(); 
    const txt = (txtRaw||'').replace(/^\uFEFF/, '').trim();
    let data; 
    
    try{ 
      data = JSON.parse(txt);
    }catch{ 
      console.warn('Categories API non-JSON:', {status, ctype, body: txt});
      return; 
    }
    
    if(!data || !data.success){ 
      console.warn('Categories API failed:', {status, ctype, body: txt}); 
      return; 
    }
    
    // Sắp xếp categories theo tên
    const categories = (data.data || []).sort((a, b) => {
      const nameA = a.product_type || a.name || '';
      const nameB = b.product_type || b.name || '';
      return nameA.localeCompare(nameB);
    });
    
    // Thêm options vào dropdown
    categories.forEach(category => {
      const id = category.id;
      const name = category.product_type || category.name || `Category ${id}`;
      
      // Dropdown cho filter
      const o1 = document.createElement('option'); 
      o1.value = id; 
      o1.textContent = name; 
      sel.appendChild(o1);
      
      // Dropdown cho form thêm/sửa
      const o2 = document.createElement('option'); 
      o2.value = id; 
      o2.textContent = name; 
      selForm.appendChild(o2);
    });
    
    console.log('Categories loaded:', categories.length);
    
  }catch(err){ 
    console.warn('loadCategories error:', err); 
  }
}

// Load products
async function loadProducts(){
  const tbody = document.querySelector('#tblProducts tbody');
  tbody.innerHTML = '<tr><td colspan="8" class="empty">Đang tải dữ liệu...</td></tr>';
  
  const q = document.getElementById('txtSearch').value.trim();
  const cat = document.getElementById('selCategory').value;
  const qs = new URLSearchParams(); 
  if(q) qs.set('search', q); 
  if(cat) qs.set('category_id', cat); 
  qs.set('limit', String(pageSize)); 
  qs.set('page', String(currentPage));
  
  const url = API_BASE + '/products?' + qs.toString();
  
  try{
    const res = await fetch(url);
    const status = res.status; 
    const ctype = res.headers.get('content-type')||'';
    const txtRaw = await res.text(); 
    const txt = (txtRaw||'').replace(/^\uFEFF/, '').trim();
    let data; 
    
    try{ 
      data = JSON.parse(txt);
    }catch{
      console.warn('Products non-JSON:', {status, ctype, body: txt});
      const snippet = txt.length ? (txt.slice(0,200) + (txt.length>200?'…':'')) : '(empty)';
      tbody.innerHTML = `<tr><td colspan="8" class="empty error">Lỗi: Phản hồi không hợp lệ từ server (HTTP ${status}, ${ctype}). Body: ${snippet}</td></tr>`;
      return;
    }
    
    if(!data.success){
      tbody.innerHTML = `<tr><td colspan="8" class="empty error">${data.message||'Không tải được dữ liệu'}</td></tr>`;
      return;
    }
    
    // Handle new response format with pagination
    let list, paginationInfo;
    if (data.data && data.data.data) {
      // New format: { success: true, data: { data: [...], pagination: {...} } }
      list = data.data.data || [];
      paginationInfo = data.data.pagination;
    } else {
      // Old format: { success: true, data: [...] }
      list = data.data || [];
      paginationInfo = null;
    }
    
    lastCount = list.length;
    
    // Update pagination info if available
    if (paginationInfo) {
      pagination = paginationInfo;
      totalPages = paginationInfo.total_pages || 1;
      currentPage = paginationInfo.current_page || 1;
    } else {
      // Fallback for old response format
      totalPages = Math.max(1, Math.ceil(lastCount / pageSize));
    }
    
    if(!list.length){ 
      tbody.innerHTML = '<tr><td colspan="8" class="empty">Không có sản phẩm nào</td></tr>'; 
      updatePagination(); 
      return; 
    }
    
    tbody.innerHTML = list.map(p=>{
      const thumbs = normalizeThumbList(p.thumbnail);
      const img = (thumbs[0]||getDefaultImage()).replace(/"/g,'&quot;');
      const editDisabled = canWrite ? '' : 'disabled';
      const deleteDisabled = canWrite ? '' : 'disabled';
      return `<tr>
        <td><strong>${p.id}</strong></td>
        <td><img class="thumb-sm" src="${img}" onerror="this.onerror=null;this.src='${getDefaultImage()}'"></td>
        <td><strong>${p.product_name||''}</strong></td>
        <td><span class="tag">${p.product_type||''}</span></td>
        <td><strong>${money(p.price)}</strong></td>
        <td>${p.stock_quantity||0}</td>
        <td>
          <button class="btn" onclick="openEdit(${p.id})" ${editDisabled}>Sửa</button>
          <button class="btn danger" onclick="confirmDelete(${p.id})" ${deleteDisabled}>Xóa</button>
        </td>
      </tr>`;
    }).join('');
    updatePagination();
  }catch(err){
    console.error('Load products error:', err);
    tbody.innerHTML = `<tr><td colspan="8" class="empty error">Lỗi kết nối: ${err.message}</td></tr>`;
    showMessage('Không thể tải dữ liệu sản phẩm', true);
  }
}

function updatePagination(){
  const pageLabel = document.getElementById('pageLabel');
  
  if (pagination && pagination.total_pages) {
    pageLabel.textContent = `Trang ${currentPage} / ${pagination.total_pages} (${pagination.total} sản phẩm)`;
  } else {
    pageLabel.textContent = `Trang ${currentPage}`;
  }
  
  document.getElementById('btnPrev').disabled = currentPage<=1;
  
  if (pagination && pagination.has_more !== undefined) {
    document.getElementById('btnNext').disabled = !pagination.has_more;
  } else {
    // Fallback for old logic
    document.getElementById('btnNext').disabled = lastCount < pageSize;
  }
}

// Edit dialog open
async function openEdit(id){
  try{
    const res = await fetch(API_BASE + '/products/' + id);
    const txtRaw = await res.text(); 
    const txt = (txtRaw||'').replace(/^\uFEFF/, '').trim();
    let data; 
    
    try{ 
      data = JSON.parse(txt);
    }catch{ 
      showMessage('Lỗi tải thông tin sản phẩm', true); 
      return; 
    }
    
    if(!data.success){ 
      showMessage(data.message||'Không tìm thấy sản phẩm', true); 
      return; 
    }
    
    const p = data.data;
    document.getElementById('dlgTitle').textContent = 'Sửa sản phẩm #' + id;
    document.getElementById('prdId').value=id;
    document.getElementById('prdName').value=p.product_name||'';
    document.getElementById('prdCategory').value=p.category_id||'';
    document.getElementById('prdType').value=p.product_type||'';
    document.getElementById('prdPrice').value=p.price||0;
    document.getElementById('prdStock').value=p.stock_quantity||0;
    document.getElementById('prdThumb').value=Array.isArray(p.thumbnail)?JSON.stringify(p.thumbnail):(p.thumbnail||'');
    document.getElementById('prdDesc').value=p.description||'';
    document.getElementById('dlgProduct').showModal();
  }catch(err){ 
    console.error('Open edit error:', err); 
    showMessage('Không thể tải thông tin sản phẩm', true); 
  }
}

// Delete product
async function confirmDelete(id){
  if(!confirm('Bạn có chắc chắn muốn xóa sản phẩm #' + id + '?')) return;
  
  try{
    const res = await fetch(API_BASE + '/products/' + id, {method:'DELETE', headers: authHeaders()});
    const txtRaw = await res.text(); 
    const txt = (txtRaw||'').replace(/^\uFEFF/, '').trim();
    let data; 
    
    try{ 
      data = JSON.parse(txt);
    }catch{ 
      showMessage('Lỗi phản hồi từ server', true); 
      return; 
    }
    
    if(!res.ok || !data.success){ 
      showMessage(data.message||'Không thể xóa sản phẩm', true); 
      return; 
    }
    
    await loadProducts(); 
    showMessage('Đã xóa sản phẩm thành công');
  }catch(err){ 
    showMessage('Không thể xóa sản phẩm', true); 
  }
}

// DOM Ready Event Handlers
function initEventHandlers() {
  // Create dialog open
  document.getElementById('btnCreate').addEventListener('click', ()=>{
    document.getElementById('dlgTitle').textContent = 'Thêm sản phẩm mới';
    document.getElementById('prdId').value='';
    document.getElementById('prdName').value='';
    document.getElementById('prdCategory').selectedIndex=0;
    document.getElementById('prdType').value='';
    document.getElementById('prdPrice').value='';
    document.getElementById('prdStock').value='0';
    document.getElementById('prdThumb').value='';
    document.getElementById('prdDesc').value='';
    document.getElementById('dlgProduct').showModal();
  });

  // Save create/update
  document.getElementById('frmProduct').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const id = document.getElementById('prdId').value.trim();
    const isEdit = !!id;
    const payload = {
      category_id: Number(document.getElementById('prdCategory').value),
      product_name: document.getElementById('prdName').value.trim(),
      product_type: document.getElementById('prdType').value.trim(),
      price: Number(document.getElementById('prdPrice').value||0),
      thumbnail: document.getElementById('prdThumb').value.trim(),
      description: document.getElementById('prdDesc').value.trim(),
      stock_quantity: Number(document.getElementById('prdStock').value||0)
    };
    
    if(!payload.product_name){ 
      showMessage('Vui lòng nhập tên sản phẩm', true); 
      return; 
    }
    if(!payload.category_id){ 
      showMessage('Vui lòng chọn danh mục', true); 
      return; 
    }
    if(payload.price<=0){ 
      showMessage('Giá sản phẩm phải lớn hơn 0', true); 
      return; 
    }
    
    const method = isEdit ? 'PUT' : 'POST';
    const url = API_BASE + '/products' + (isEdit? '/' + id : '');
    
    try{
      const res = await fetch(url, {method, headers: authHeaders(), body: JSON.stringify(payload)});
      const txtRaw = await res.text(); 
      const txt = (txtRaw||'').replace(/^\uFEFF/, '').trim();
      let data; 
      
      try{ 
        data = JSON.parse(txt);
      }catch(parseErr){ 
        console.error('JSON Parse Error:', parseErr);
        console.error('Response Status:', res.status);
        console.error('Response Headers:', res.headers.get('content-type'));
        console.error('Raw Response:', txt.slice(0, 500)); // First 500 chars
        showMessage(`Lỗi phản hồi từ server (${res.status}). Xem Console để debug.`, true); 
        return; 
      }
      
      if(!res.ok || !data.success){ 
        showMessage(data.message||`Lỗi ${isEdit?'cập nhật':'tạo'} sản phẩm`, true); 
        return; 
      }
      
      document.getElementById('dlgProduct').close();
      await loadProducts();
      showMessage(isEdit?'Đã cập nhật sản phẩm thành công':'Đã tạo sản phẩm thành công');
    }catch(err){ 
      showMessage('Không thể lưu sản phẩm', true); 
    }
  });

  // Events
  document.getElementById('btnCancel').addEventListener('click', ()=>{ 
    document.getElementById('dlgProduct').close(); 
  });
  
  document.getElementById('btnSearch').addEventListener('click', ()=>{ 
    currentPage=1; 
    loadProducts(); 
  });
  
  document.getElementById('btnClear').addEventListener('click', ()=>{ 
    document.getElementById('txtSearch').value=''; 
    document.getElementById('selCategory').value=''; 
    currentPage=1; 
    loadProducts(); 
  });
  
  document.getElementById('txtSearch').addEventListener('keydown', (e)=>{ 
    if(e.key==='Enter'){ 
      e.preventDefault(); 
      currentPage=1; 
      loadProducts(); 
    }
  });
  
  document.getElementById('selCategory').addEventListener('change', ()=>{ 
    currentPage=1; 
    loadProducts(); 
  });
  
  document.getElementById('btnPrev').addEventListener('click', ()=>{ 
    if(currentPage>1){ 
      currentPage--; 
      loadProducts(); 
    }
  });
  
  document.getElementById('btnNext').addEventListener('click', ()=>{ 
    if(lastCount>=pageSize){ 
      currentPage++; 
      loadProducts(); 
    }
  });
}

// Init function
async function init(){
  await resolveApiBase();
  document.getElementById('btnCreate').disabled = !canWrite;
  const notice = document.getElementById('noticeAuth');
  notice.textContent = 'Yêu cầu đăng nhập để thêm/sửa/xóa';
  notice.style.display = canWrite ? 'none' : 'block';
  await loadCategories();
  await loadProducts();
}

// Make functions globally available
window.openEdit = openEdit;
window.confirmDelete = confirmDelete;

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  initEventHandlers();
  init();
});

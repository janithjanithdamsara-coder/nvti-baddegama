/**
 * NVTI Baddegama National Diploma Portal JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
  // Demo Account Quick Fill Helper
  window.fillLogin = function (email, password) {
    const emailField = document.getElementById('loginEmail');
    const passField = document.getElementById('loginPassword');
    if (emailField && passField) {
      emailField.value = email;
      passField.value = password;
      emailField.classList.add('is-valid');
      passField.classList.add('is-valid');
      setTimeout(() => {
        emailField.classList.remove('is-valid');
        passField.classList.remove('is-valid');
      }, 1500);
    }
  };

  // Client-side file size guard for uploads
  const fileInputs = document.querySelectorAll('input[type="file"]');
  fileInputs.forEach((input) => {
    input.addEventListener('change', function () {
      if (this.files && this.files[0]) {
        const sizeInMb = this.files[0].size / (1024 * 1024);
        if (sizeInMb > 25) {
          alert('Warning: Selected file size is ' + sizeInMb.toFixed(1) + 'MB. Maximum allowed is 25MB.');
          this.value = '';
        }
      }
    });
  });

  // Auto-dismiss alerts after 5 seconds
  const autoAlerts = document.querySelectorAll('.alert-dismissible');
  autoAlerts.forEach((alert) => {
    setTimeout(() => {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
      if (bsAlert) bsAlert.close();
    }, 5000);
  });

  // Initialize Lucide Icons
  if (typeof lucide !== 'undefined' && lucide.createIcons) {
    lucide.createIcons();
  }

  // Modern Sidebar Collapse / Expand Toggle
  const appSidebar = document.getElementById('appSidebar') || document.querySelector('.sidebar');

  if (appSidebar) {
    // 1. Mobile Backdrop
    let backdrop = document.querySelector('.app-sidebar-backdrop');
    if (!backdrop) {
      backdrop = document.createElement('div');
      backdrop.className = 'app-sidebar-backdrop';
      document.body.appendChild(backdrop);
    }

    // 2. Floating Expand Button (Always available to un-hide)
    let floatingBtn = document.querySelector('.sidebar-floating-expand-btn');
    if (!floatingBtn) {
      floatingBtn = document.createElement('button');
      floatingBtn.className = 'sidebar-floating-expand-btn';
      floatingBtn.id = 'appSidebarFloatingExpandBtn';
      floatingBtn.type = 'button';
      floatingBtn.innerHTML = '<i data-lucide="panel-left-open" style="width: 16px; height: 16px;"></i><span>Expand Menu</span>';
      document.body.appendChild(floatingBtn);
    }

    function setSidebarState(isCollapsed) {
      if (isCollapsed) {
        appSidebar.classList.add('collapsed');
        document.body.classList.add('has-collapsed-sidebar');
        localStorage.setItem('nvti_sidebar_collapsed', '1');
      } else {
        appSidebar.classList.remove('collapsed');
        document.body.classList.remove('has-collapsed-sidebar');
        localStorage.setItem('nvti_sidebar_collapsed', '0');
      }

      // Update navbar toggle icon with clean fresh element for Lucide (Hamburger on Mobile, Panel on Desktop)
      const toggleBtns = document.querySelectorAll('#appSidebarToggleBtn, .sidebar-topbar-toggle-btn');
      toggleBtns.forEach((btn) => {
        btn.setAttribute('title', isCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar');
        btn.innerHTML = isCollapsed
          ? '<i data-lucide="menu" class="d-lg-none" style="width: 20px; height: 20px;"></i><i data-lucide="panel-left-open" class="sidebar-toggle-icon d-none d-lg-inline-block" style="width: 18px; height: 18px;"></i>'
          : '<i data-lucide="menu" class="d-lg-none" style="width: 20px; height: 20px;"></i><i data-lucide="panel-left-close" class="sidebar-toggle-icon d-none d-lg-inline-block" style="width: 18px; height: 18px;"></i>';
      });

      // Update sidebar inline collapse icon
      const inlineBtns = document.querySelectorAll('#appSidebarInlineToggleBtn, .sidebar-inline-toggle-btn');
      inlineBtns.forEach((btn) => {
        btn.setAttribute('title', isCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar');
        btn.innerHTML = isCollapsed
          ? '<i data-lucide="chevron-right" class="sidebar-inline-toggle-icon" style="width: 16px; height: 16px;"></i>'
          : '<i data-lucide="chevron-left" class="sidebar-inline-toggle-icon" style="width: 16px; height: 16px;"></i>';
      });

      if (floatingBtn) {
        floatingBtn.style.display = (isCollapsed && window.innerWidth >= 992) ? 'inline-flex' : 'none';
      }

      if (typeof lucide !== 'undefined' && lucide.createIcons) {
        lucide.createIcons();
      }
    }

    function closeMobileSidebar() {
      appSidebar.classList.remove('show-mobile');
      backdrop.classList.remove('active');
    }

    // Click handler for toggle buttons & mobile close button
    document.addEventListener('click', function (e) {
      if (e.target.closest('#appSidebarMobileCloseBtn')) {
        e.preventDefault();
        closeMobileSidebar();
        return;
      }

      const targetBtn = e.target.closest('#appSidebarToggleBtn, #appSidebarToggle, #appSidebarFloatingExpandBtn, #appSidebarInlineToggleBtn, .sidebar-topbar-toggle-btn');
      if (targetBtn) {
        e.preventDefault();
        e.stopPropagation();

        if (window.innerWidth < 992) {
          // Mobile drawer toggle
          appSidebar.classList.toggle('show-mobile');
          backdrop.classList.toggle('active', appSidebar.classList.contains('show-mobile'));
        } else {
          // Desktop collapse / expand
          const isNowCollapsed = !appSidebar.classList.contains('collapsed');
          setSidebarState(isNowCollapsed);
        }
      }
    });

    // Close mobile sidebar when clicking any menu link or backdrop
    backdrop.addEventListener('click', closeMobileSidebar);
    appSidebar.querySelectorAll('.sidebar-link').forEach((link) => {
      link.addEventListener('click', function () {
        if (window.innerWidth < 992) {
          closeMobileSidebar();
        }
      });
    });

    // Restore desktop collapsed state on page load
    if (window.innerWidth >= 992) {
      const savedState = localStorage.getItem('nvti_sidebar_collapsed');
      if (savedState === '1') {
        setSidebarState(true);
      } else {
        setSidebarState(false);
      }
    }
  }
});

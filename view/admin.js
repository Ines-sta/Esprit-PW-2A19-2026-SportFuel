document.addEventListener('DOMContentLoaded', function() {
  let editMode = false;
  let currentEditId = null;

  // Modal logic
  window.openModal = function(isEdit, user_data = null) { 
    const modalObj = document.getElementById('modal');
    const title = modalObj.querySelector('.modal-title');
    const btn = modalObj.querySelector('.btn-primary');
    
    if(isEdit && user_data) {
      editMode = true;
      currentEditId = user_data.id;
      title.innerText = "✏️ Modifier l'utilisateur";
      btn.innerText = "Enregistrer les modifications";
      
      document.getElementById('modNom').value = user_data.nom || '';
      document.getElementById('modEmail').value = user_data.email || '';
      document.getElementById('modPass').value = ''; 
      document.getElementById('modRole').value = user_data.role || 'Sportif';
      document.getElementById('modStatut').value = user_data.statut || 'Actif';
      document.getElementById('modAge').value = user_data.age || '';
      document.getElementById('modSport').value = user_data.sport || 'Marathon';
    } else {
      editMode = false;
      currentEditId = null;
      title.innerText = "➕ Ajouter un utilisateur";
      btn.innerText = "Créer l'utilisateur";
      document.getElementById('modNom').value = '';
      document.getElementById('modEmail').value = '';
      document.getElementById('modPass').value = '';
      document.getElementById('modRole').selectedIndex = 0;
      document.getElementById('modStatut').selectedIndex = 0;
      document.getElementById('modAge').value = '';
      document.getElementById('modSport').selectedIndex = 0;
    }
    modalObj.classList.add('open'); 
  };

  window.closeModal = function() { 
    document.getElementById('modal').classList.remove('open'); 
  };

  
  const modalOverlay = document.getElementById('modal');
  if (modalOverlay) {
    modalOverlay.addEventListener('click', function(e) { 
      if (e.target === this) closeModal(); 
    });
  }

  
  const usersTable = document.getElementById('usersTable');
  if (usersTable) {
    usersTable.addEventListener('click', function(e) {
      const btn = e.target.closest('.action-btn');
      if (!btn) return;
      
      const row = btn.closest('tr');
      const uId = row.dataset.id;
      const uName = row.dataset.nom || 'cet utilisateur';

      if (btn.classList.contains('delete')) {
        if (confirm(`⚠️ Voulez-vous vraiment supprimer DÉFINITIVEMENT l'utilisateur : ${uName} ?`)) {
          fetch('/SportFuel-Module1/controller/AdminController.php?action=delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: uId })
          })
          .then(r => r.json())
          .then(data => {
            if (data.success) {
              row.style.transition = 'opacity 0.4s, transform 0.4s';
              row.style.opacity = '0';
              row.style.transform = 'translateX(20px)';
              setTimeout(() => row.remove(), 400);
            } else {
              alert('❌ Erreur : ' + (data.message || 'Impossible de supprimer.'));
            }
          })
          .catch(err => alert('❌ Erreur de connexion au serveur.'));
        }
      } else if (btn.classList.contains('edit')) {
        const udata = {
          id: uId,
          nom: row.dataset.nom,
          email: row.dataset.email,
          role: row.dataset.role,
          statut: row.dataset.statut,
          sport: row.dataset.sport,
          age: row.dataset.age
        };
        openModal(true, udata);
      }
    });
  }

  
  const saveBtn = document.querySelector('.modal-footer .btn-primary');
  if (saveBtn) {
    saveBtn.addEventListener('click', function() {
      const data = {
        nom: document.getElementById('modNom').value.trim(),
        email: document.getElementById('modEmail').value.trim(),
        password: document.getElementById('modPass').value,
        role: document.getElementById('modRole').value,
        statut: document.getElementById('modStatut').value,
        age: document.getElementById('modAge').value,
        sport: document.getElementById('modSport').value
      };

      if (!data.nom || data.nom.length < 3) {
        alert('📢 Le nom doit contenir au moins 3 caractères.');
        return;
      }

      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(data.email)) {
        alert('📢 Veuillez entrer une adresse email valide.');
        return;
      }

      if (!editMode && data.password.length < 6) {
        alert('📢 Le mot de passe doit contenir au moins 6 caractères pour un nouvel utilisateur.');
        return;
      }

      if (data.age && (isNaN(data.age) || data.age < 1 || data.age > 100)) {
        alert('📢 Veuillez entrer un âge valide (1-100).');
        return;
      }

      if (editMode) {
        data.id = currentEditId;
      }

      const endpoint = editMode ? '/SportFuel-Module1/controller/AdminController.php?action=edit' : '/SportFuel-Module1/controller/AdminController.php?action=add';

      fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          alert(editMode ? '✅ Modifications enregistrées avec succès !' : '✅ Utilisateur créé avec succès !');
          location.reload();
        } else {
          alert('❌ Erreur : ' + (res.message || 'Action impossible.'));
        }
      })
      .catch(err => alert('❌ Erreur de connexion au serveur.'));
    });
  }

  
  const searchInput = document.getElementById('searchInput');
  const roleFilter = document.getElementById('roleFilter');
  const statusFilter = document.getElementById('statusFilter');

  function filterTable() {
    const searchVal = searchInput.value.toLowerCase().trim();
    const roleVal = roleFilter.value.toLowerCase();
    const statusVal = statusFilter.value.toLowerCase();

    document.querySelectorAll('#usersTable tr').forEach(row => {
      const nameText = row.querySelector('.user-name')?.innerText.toLowerCase() || '';
      const emailText = row.querySelector('.user-email')?.innerText.toLowerCase() || '';
      const roleText = row.querySelector('td:nth-child(4)')?.innerText.toLowerCase() || '';
      const statusText = row.querySelector('td:nth-child(5)')?.innerText.toLowerCase() || '';
      
      const matchesSearch = nameText.includes(searchVal) || emailText.includes(searchVal);
      const matchesRole = (roleVal === '' || roleText.includes(roleVal));
      const matchesStatus = (statusVal === '' || statusText.includes(statusVal));

      row.style.display = (matchesSearch && matchesRole && matchesStatus) ? '' : 'none';
    });
  }

  if (searchInput) searchInput.addEventListener('input', filterTable);
  if (roleFilter) roleFilter.addEventListener('change', filterTable);
  if (statusFilter) statusFilter.addEventListener('change', filterTable);

});

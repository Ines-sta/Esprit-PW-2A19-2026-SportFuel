window.addEventListener('DOMContentLoaded', () => {
  let editMode = false;
  const saveBar = document.getElementById('saveBar');
  const inputs = document.querySelectorAll('.field-input, .field-select');
  const sportTags = document.querySelectorAll('.sport-tag');
  const sportInput = document.getElementById('sport');
  const roleInput = document.getElementById('profileRole');
  const currentRole = (roleInput ? roleInput.value : 'sportif').toLowerCase();
  const profilePhotoInput = document.getElementById('profilePhotoInput');
  const profileAvatar = document.getElementById('profileAvatar');

  const nomInput = document.getElementById('nom');
  if (nomInput) {
    nomInput.addEventListener('input', function() {
      this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
    });
  }

  if (profilePhotoInput && profileAvatar) {
    profilePhotoInput.addEventListener('change', function () {
      const file = this.files && this.files[0] ? this.files[0] : null;
      if (!file) return;

      const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
      if (!allowedTypes.includes(file.type)) {
        alert('❌ Format non supporté (JPEG, PNG, WebP, GIF).');
        this.value = '';
        return;
      }
      if (file.size > 5 * 1024 * 1024) {
        alert('❌ Image trop volumineuse (max 5 Mo).');
        this.value = '';
        return;
      }

      const payload = new FormData();
      payload.append('image', file);
      profileAvatar.classList.add('uploading');

      fetch('/Esprit-PW-2A19-2026-SportFuel/Controller/api/api.php?action=upload_profile_photo', {
        method: 'POST',
        body: payload
      })
      .then(response => response.json())
      .then(result => {
        if (!result.success || !result.photo_profil_url) {
          throw new Error(result.message || 'Upload impossible');
        }

        const img = document.createElement('img');
        img.id = 'profileAvatarImg';
        img.alt = 'Photo de profil';
        img.src = result.photo_profil_url;

        profileAvatar.innerHTML = '';
        profileAvatar.appendChild(img);
        alert('✅ Photo de profil mise à jour.');
      })
      .catch(error => {
        alert('❌ ' + (error.message || 'Erreur lors de l\'upload'));
      })
      .finally(() => {
        profileAvatar.classList.remove('uploading');
        profilePhotoInput.value = '';
      });
    });
  }

  // Contrôle numérique pour la fréquence et autres futurs champs
  inputs.forEach(inp => {
    if (inp.id === 'frequence' || inp.type === 'number' || inp.getAttribute('inputmode') === 'numeric') {
        inp.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
  });

  sportTags.forEach(tag => {
    tag.addEventListener('click', function() {
      if (!editMode) return; // Only allow changing sport if in edit mode
      sportTags.forEach(t => t.classList.remove('active')); 
      this.classList.add('active');
      if(sportInput) sportInput.value = this.getAttribute('data-value');
    });
  });

  window.toggleEdit = function() {
    editMode = !editMode;
    inputs.forEach(inp => {
      if (inp.id === 'email' || inp.id === 'mdp') return;
      const canEdit = inp.getAttribute('data-editable') === '1';
      if (!canEdit) {
        inp.disabled = true;
        return;
      }
      
      inp.style.background = editMode ? 'white' : 'var(--cream)';
      inp.style.borderColor = editMode ? 'var(--green-mid)' : 'var(--border)';
      if (inp.tagName === 'INPUT' || inp.tagName === 'SELECT') {
        inp.disabled = !editMode;
      }
    });
    saveBar.style.display = editMode ? 'flex' : 'none';
  };

  window.saveProfile = function() {
    const nomElement = document.getElementById('nom');
    const data = {};

    if (nomElement) {
      data.nom = nomElement.value.trim();
    }

    if (currentRole === 'sportif') {
      if (sportInput) data.sport = sportInput.value;

      const objectifElement = document.getElementById('objectif');
      if (objectifElement) data.objectif = objectifElement.value;

      const niveauElement = document.getElementById('niveau');
      if (niveauElement) data.niveau = niveauElement.value;

      const frequenceElement = document.getElementById('frequence');
      if (frequenceElement) data.frequence = frequenceElement.value;
    }

    // JS Validation
    if (!data.nom || data.nom.length < 3) {
        alert('❌ Le nom est trop court.');
        return;
    }

    if (Object.prototype.hasOwnProperty.call(data, 'frequence') && (isNaN(data.frequence) || data.frequence < 0 || data.frequence > 21)) {
        alert('❌ La fréquence hebdomadaire doit être entre 0 et 21.');
        return;
    }

    fetch('/Esprit-PW-2A19-2026-SportFuel/Controller/api/api.php?action=save_profil', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        editMode = false;
        inputs.forEach(inp => {
          inp.style.background = 'var(--cream)';
          inp.style.borderColor = 'var(--border)';
          inp.disabled = true;
        });
        saveBar.style.display = 'none';
        alert('✅ ' + result.message);
        window.location.reload(); // Reload to see the changes applied dynamically
      } else {
        alert('❌ Erreur: ' + result.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('❌ Une erreur est survenue lors de la sauvegarde.');
    });
  };

  inputs.forEach(inp => {
    inp.disabled = true;
  });
  saveBar.style.display = 'none';

  window.deleteAccount = function() {
    if (confirm("Voulez-vous VRAIMENT supprimer votre compte ? Cette action est totalement irréversible.")) {
      fetch('/Esprit-PW-2A19-2026-SportFuel/Controller/api/api.php?action=delete_account', { method: 'POST' })
      .then(r => r.json())
      .then(res => {
         if (res.success) {
            alert('Compte supprimé définitivement.');
          window.location.href = '/Esprit-PW-2A19-2026-SportFuel/index.php?page=auth&action=logout';
         } else {
            alert('Erreur: ' + (res.message || 'inconnue'));
         }
      })
      .catch(e => alert('Erreur serveur.'));
    }
  };
});
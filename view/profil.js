window.addEventListener('DOMContentLoaded', () => {
  let editMode = false;
  const saveBar = document.getElementById('saveBar');
  const inputs = document.querySelectorAll('.field-input, .field-select');
  const sportTags = document.querySelectorAll('.sport-tag');
  const sportInput = document.getElementById('sport');

  const nomInput = document.getElementById('nom');
  if (nomInput) {
    nomInput.addEventListener('input', function() {
      this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
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
      // Don't enable email field since we use it as identifier usually, 
      // but let's allow it if we want. For safety, let's keep email disabled if it has id="email"
      if (inp.id === 'email' || inp.id === 'mdp') return;
      
      inp.style.background = editMode ? 'white' : 'var(--cream)';
      inp.style.borderColor = editMode ? 'var(--green-mid)' : 'var(--border)';
      if (inp.tagName === 'INPUT' || inp.tagName === 'SELECT') {
        inp.disabled = !editMode;
      }
    });
    saveBar.style.display = editMode ? 'flex' : 'none';
  };

  window.saveProfile = function() {
    const nom = document.getElementById('nom').value.trim();
    const age = document.getElementById('age')?.innerText || document.getElementById('age')?.value; // Check if it's value or text
    const poids = document.getElementById('poids')?.value || '';
    const taille = document.getElementById('taille')?.value || '';
    
    
    const data = {
        nom: nom,
        sport: sportInput ? sportInput.value : 'Marathon',
        objectif: document.getElementById('objectif')?.value || 'Performance',
        niveau: document.getElementById('niveau')?.value || 'Débutant',
        frequence: document.getElementById('frequence')?.value || '5'
    };

    // JS Validation
    if (data.nom.length < 3) {
        alert('❌ Le nom est trop court.');
        return;
    }

    if (isNaN(data.frequence) || data.frequence < 0 || data.frequence > 21) {
        alert('❌ La fréquence hebdomadaire doit être entre 0 et 21.');
        return;
    }

    fetch('/Esprit-PW-2A19-2526-SportFuel/controller/ProfilController.php?action=save', {
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
      fetch('/Esprit-PW-2A19-2526-SportFuel/controller/ProfilController.php?action=deleteAccount', { method: 'POST' })
      .then(r => r.json())
      .then(res => {
         if (res.success) {
            alert('Compte supprimé définitivement.');
          window.location.href = '/Esprit-PW-2A19-2526-SportFuel/controller/AuthController.php?action=logout';
         } else {
            alert('Erreur: ' + (res.message || 'inconnue'));
         }
      })
      .catch(e => alert('Erreur serveur.'));
    }
  };
});
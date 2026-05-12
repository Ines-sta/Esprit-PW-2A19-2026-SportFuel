document.addEventListener('DOMContentLoaded', function () {

  const roleInput  = document.getElementById('roleInput');
  const submitBtn  = document.getElementById('submitBtn');
  const btnSportif = document.getElementById('btnSportif');
  const btnAdmin   = document.getElementById('btnAdmin');
  const nomInput   = document.getElementById('nom');
  const emailInput = document.getElementById('email');
  const ageInput   = document.getElementById('age');
  const poidsInput = document.getElementById('poids');
  const tailleInput = document.getElementById('taille');
  
  // Face ID Elements
  const faceIdModal = document.getElementById('faceIdModal');
  const activateFaceIdBtn = document.getElementById('activateFaceIdBtn');
  const skipFaceIdBtn = document.getElementById('skipFaceIdBtn');
  const manualFaceIdBtn = document.getElementById('manualFaceIdBtn');
  const webcamReg = document.getElementById('webcamReg');
  const FACEID_KEY = 'sportfuel_face_descriptor';
  const MODEL_URL = 'https://raw.githack.com/justadudewhohacks/face-api.js/master/weights';
  let modelsLoaded = false;
  let pendingFaceDescriptor = null; // En mémoire avant l'inscription


  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('role') === 'Admin') selectRole('Admin');

  // Contrôle de saisie pour le nom (lettres et espaces uniquement)
  if (nomInput) {
    nomInput.addEventListener('input', function() {
      this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
    });
  }

  // Contrôle de saisie pour l'âge, le poids et la taille (chiffres uniquement)
  const numericFields = [ageInput, poidsInput, tailleInput];
  numericFields.forEach(field => {
    if (field) {
      field.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
      });
    }
  });

  function selectRole(role) {
    if (roleInput) roleInput.value = role;

    if (role === 'Admin') {
      btnAdmin.classList.add('sel-admin');
      btnAdmin.classList.remove('sel-sportif');
      btnSportif.classList.remove('sel-sportif', 'sel-admin');
      submitBtn.textContent = '⭐ S\'inscrire comme Admin';
      submitBtn.classList.add('admin-mode');
      ageInput.style.display = 'none';
      poidsInput.style.display = 'none';
      tailleInput.style.display = 'none';
      if (ageInput) ageInput.value = '';
      if (poidsInput) poidsInput.value = '';
      if (tailleInput) tailleInput.value = '';
    } else {
      btnSportif.classList.add('sel-sportif');
      btnSportif.classList.remove('sel-admin');
      btnAdmin.classList.remove('sel-admin', 'sel-sportif');
      submitBtn.textContent = '🏃 S\'inscrire comme Sportif';
      submitBtn.classList.remove('admin-mode');
      ageInput.style.display = '';
      poidsInput.style.display = '';
      tailleInput.style.display = '';
    }
  }

  btnSportif.addEventListener('click', () => selectRole('Sportif'));
  btnAdmin.addEventListener('click',   () => selectRole('Admin'));

  
  document.getElementById('inscriptionForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const nom             = document.getElementById('nom').value.trim();
    const email           = document.getElementById('email').value.trim();
    const password        = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const age             = document.getElementById('age').value.trim();
    const poids           = document.getElementById('poids').value.trim();
    const taille          = document.getElementById('taille').value.trim();
    const emailRegex      = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (nom.length < 3)              { alert('❌ Le nom doit contenir au moins 3 caractères.'); return; }
    if (!emailRegex.test(email))     { alert('❌ Email invalide.'); return; }
    if (password.length < 6)         { alert('❌ Mot de passe trop court (min 6 car.).'); return; }
    if (password !== confirmPassword){ alert('❌ Les mots de passe ne correspondent pas.'); return; }
    if (roleInput.value === 'Sportif') {
      if (isNaN(age) || age < 1 || age > 100) { alert('❌ Âge invalide (1-100).'); return; }
      if (isNaN(poids) || poids <= 0)  { alert('❌ Poids invalide.'); return; }
      if (isNaN(taille) || taille <= 0){ alert('❌ Taille invalide.'); return; }
    }

    const formData = new FormData(this);
    fetch(this.action, {
      method: 'POST',
      body: formData,
      credentials: 'include', // Assure le transfert de la session
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.text())
    .then(text => {
      if (text.includes('successfully') || text.includes('compte créé') || text.toLowerCase().includes('success')) {
        if (pendingFaceDescriptor) {
            // Si on avait déjà capturé le visage avant de cliquer sur s'inscrire
            saveFaceToDatabase(pendingFaceDescriptor);
        } else {
            showFaceIdModal();
        }
      } else {
        alert("✅ Compte créé !");
        if (pendingFaceDescriptor) saveFaceToDatabase(pendingFaceDescriptor);
        else showFaceIdModal();
      }
    })
    .catch(() => {
      alert("❌ Erreur serveur.");
    });
  });

  async function loadModels() {
    if (modelsLoaded) return true;
    try {
        activateFaceIdBtn.textContent = "IA en chargement...";
        activateFaceIdBtn.disabled = true;
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
            faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
        ]);
        modelsLoaded = true;
        activateFaceIdBtn.textContent = "Capturer mon visage";
        activateFaceIdBtn.disabled = false;
        return true;
    } catch (e) {
        console.error(e);
        return false;
    }
  }

  async function showFaceIdModal() {
    faceIdModal.style.display = 'flex';
    await loadModels();
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
        webcamReg.srcObject = stream;
    } catch (e) {
        alert("❌ Caméra inaccessible.");
    }
  }

  async function startFaceIDRegistration() {
    activateFaceIdBtn.textContent = "Analyse...";
    activateFaceIdBtn.disabled = true;

    setTimeout(async () => {
        const detection = await faceapi.detectSingleFace(webcamReg, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (detection) {
            const descriptor = Array.from(detection.descriptor);
            pendingFaceDescriptor = descriptor; // On garde en mémoire
            
            // On essaie de sauvegarder (si déjà connecté)
            await saveFaceToDatabase(descriptor, false); 
            
            if (webcamReg.srcObject) {
                webcamReg.srcObject.getTracks().forEach(track => track.stop());
            }
            
            faceIdModal.style.display = 'none';
            manualFaceIdBtn.innerHTML = "✅ Visage prêt pour l'inscription";
            manualFaceIdBtn.style.borderColor = "#52b788";
        } else {
            alert("❌ Aucun visage détecté. Rapprochez-vous.");
            activateFaceIdBtn.textContent = "Réessayer";
            activateFaceIdBtn.disabled = false;
        }
    }, 1000);
  }

  async function saveFaceToDatabase(descriptor, redirect = true) {
    try {
        const response = await fetch('../../Controller/api/api.php?action=save_face_descriptor', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ descriptor: descriptor })
        });
        const res = await response.json();
        if (res.success && redirect) {
            alert("✅ Compte et Face ID créés avec succès !");
            window.location.href = 'connexion.html';
        }
    } catch (err) {
        console.error("Erreur save DB:", err);
    }
  }

  if (activateFaceIdBtn) {
    activateFaceIdBtn.addEventListener('click', startFaceIDRegistration);
  }

  if (manualFaceIdBtn) {
    manualFaceIdBtn.addEventListener('click', (e) => {
        e.preventDefault();
        showFaceIdModal();
    });
  }

  if (skipFaceIdBtn) {
    skipFaceIdBtn.addEventListener('click', () => {
        if (webcamReg.srcObject) {
            webcamReg.srcObject.getTracks().forEach(track => track.stop());
        }
        window.location.href = 'connexion.html';
    });
  }

  // Détection du retour de redirection Google (si nécessaire, sinon peut être supprimé)

  
  const linkBtn = document.querySelector('.link-btn');
  if (linkBtn) {
    linkBtn.addEventListener('click', function (e) {
      e.preventDefault();
      window.location.href = 'connexion.html';
    });
  }
});

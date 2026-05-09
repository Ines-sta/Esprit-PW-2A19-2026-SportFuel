document.addEventListener('DOMContentLoaded', function () {
    const API = '../Controller/api.php';
    const connexionForm = document.getElementById('connexionForm');
    const authTitle = document.getElementById('authTitle');
    const forgotProgress = document.getElementById('forgotProgress');
    const progressFill = document.getElementById('progressFill');
    const progressLabel = document.getElementById('progressLabel');
    const forgotLink = document.getElementById('forgotLink');

    const viewLogin = document.getElementById('viewLogin');
    const viewStep1 = document.getElementById('viewStep1');
    const viewStep2 = document.getElementById('viewStep2');
    const viewStep3 = document.getElementById('viewStep3');
    const viewBanned = document.getElementById('viewBanned');
    const views = [viewLogin, viewStep1, viewStep2, viewStep3, viewBanned];

    const forgotEmail = document.getElementById('forgotEmail');
    const sendCodeBtn = document.getElementById('sendCodeBtn');
    const verifyOtpBtn = document.getElementById('verifyOtpBtn');
    const otpInfo = document.getElementById('otpInfo');
    const otpInputs = Array.from(document.querySelectorAll('.otp-input'));
    const newPassword = document.getElementById('newPassword');
    const confirmNewPassword = document.getElementById('confirmNewPassword');
    const savePasswordBtn = document.getElementById('savePasswordBtn');
    const resetFeedback = document.getElementById('resetFeedback');

    const backToLogin1 = document.getElementById('backToLogin1');
    const backToStep1 = document.getElementById('backToStep1');
    const backToLogin3 = document.getElementById('backToLogin3');
    
    // Face ID Elements
    const faceIdBadge = document.getElementById('faceIdBadge');
    const faceIdBtn = document.getElementById('faceIdBtn');
    const scanOverlay = document.getElementById('scanOverlay');
    const scanMsg = document.getElementById('scanMsg');
    const faceIdModal = document.getElementById('faceIdModal');
    const activateFaceIdBtn = document.getElementById('activateFaceIdBtn');
    const skipFaceIdBtn = document.getElementById('skipFaceIdBtn');
    const webcam = document.getElementById('webcam');

    const FACEID_KEY = 'sportfuel_face_descriptor';
    const MODEL_URL = 'https://raw.githack.com/justadudewhohacks/face-api.js/master/weights';
    let modelsLoaded = false;

    let otpEmail = '';

    function showView(viewEl) {
        views.forEach(function (view) {
            view.classList.remove('active');
        });
        viewEl.classList.add('active');
    }

    function setForgotStep(step) {
        authTitle.textContent = 'Mot de passe oublié';
        forgotProgress.classList.add('active');
        progressFill.style.width = (step * 33.33) + '%';
        progressLabel.textContent = 'Étape ' + step + ' / 3';
    }

    function resetForgotFlow() {
        otpEmail = '';
        forgotEmail.value = '';
        otpInputs.forEach(function (input) { input.value = ''; });
        newPassword.value = '';
        confirmNewPassword.value = '';
        resetFeedback.textContent = '';
        resetFeedback.className = 'reset-feedback';
        forgotProgress.classList.remove('active');
        authTitle.textContent = 'Connexion';
        showView(viewLogin);
    }
    window.backToLoginFlowInternal = resetForgotFlow;

    const loginBtn = document.getElementById('loginBtn');
    const loginError = document.getElementById('loginError');
    const attemptIcons = document.getElementById('attemptIcons');
    const passwordInput = document.getElementById('password');

    let loginAttempts = 0;
    let lockoutTimer = null;

    function checkLockout() {
        const lockoutEnd = localStorage.getItem('loginLockoutEnd');
        if (lockoutEnd) {
            const timeLeft = Math.ceil((parseInt(lockoutEnd) - Date.now()) / 1000);
            if (timeLeft > 0) {
                startLockout(timeLeft);
            } else {
                localStorage.removeItem('loginLockoutEnd');
            }
        }
    }

    function startLockout(seconds) {
        loginAttempts = 3;
        passwordInput.disabled = true;
        loginBtn.disabled = true;
        
        let remaining = seconds;
        updateLockoutMessage(remaining);

        if (lockoutTimer) clearInterval(lockoutTimer);
        lockoutTimer = setInterval(() => {
            remaining--;
            if (remaining <= 0) {
                clearInterval(lockoutTimer);
                resetLockout();
            } else {
                updateLockoutMessage(remaining);
            }
        }, 1000);
    }

    function updateLockoutMessage(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        loginError.textContent = `Trop de tentatives. Réessayez dans ${mins}:${secs < 10 ? '0' : ''}${secs}`;
    }

    function resetLockout() {
        loginAttempts = 0;
        passwordInput.disabled = false;
        loginBtn.disabled = false;
        loginError.textContent = '';
        attemptIcons.innerHTML = '';
        localStorage.removeItem('loginLockoutEnd');
    }

    function addAttemptIcon() {
        const dot = document.createElement('span');
        dot.textContent = '🔴';
        attemptIcons.appendChild(dot);
    }

    function shakeField() {
        passwordInput.classList.add('shake');
        setTimeout(() => passwordInput.classList.remove('shake'), 400);
    }

    checkLockout();

    if (connexionForm) {
        connexionForm.addEventListener('submit', function (e) {
            e.preventDefault();

            if (loginAttempts >= 3) return;

            const email = document.getElementById('email').value.trim();
            const password = passwordInput.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailRegex.test(email)) {
                alert('❌ Veuillez entrer une adresse email valide.');
                return;
            }

            // Envoyer en AJAX
            const formData = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.text()) // On s'attend à du JSON ou du script d'erreur
            .then(text => {
                try {
                    const res = JSON.parse(text);
                    if (res.success) {
                        window.location.href = res.redirect;
                    } else {
                        handleLoginFailure(res.message);
                    }
                } catch (e) {
                    // Si c'est l'ancien code qui renvoie du JS (alert)
                    if (text.includes('incorrect') || text.includes('compte trouvé')) {
                        handleLoginFailure('Email ou mot de passe incorrect');
                    }
                }
            })
            .catch(() => {
                alert('❌ Erreur de connexion au serveur.');
            });
        });
    }

    function handleLoginFailure(msg) {
        if (msg === 'banned') {
            showView(viewBanned);
            authTitle.textContent = 'Accès Refusé';
            return;
        }
        loginAttempts++;
        shakeField();
        addAttemptIcon();

        if (loginAttempts < 3) {
            loginError.textContent = `Mot de passe incorrect. Il vous reste ${3 - loginAttempts} tentative(s)`;
        } else {
            const end = Date.now() + 60000;
            localStorage.setItem('loginLockoutEnd', end);
            startLockout(60);
        }
    }

    window.startForgotPasswordFlowInternal = function () {
        setForgotStep(1);
        showView(viewStep1);
        forgotEmail.focus();
    };

    if (forgotLink) {
        // Double événement géré dans HTML onclick="startForgotPasswordFlow(event)"
    }
    window.sendOtpCodeFlowInternal = function () {
        const email = forgotEmail.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('❌ Entrez un Gmail valide.');
            return;
        }

        fetch(API + '?action=send_reset_code', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email })
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res.success) {
                    alert('❌ ' + (res.message || 'Erreur lors de l’envoi du code.'));
                    return;
                }

                otpEmail = email;
                window.__sportfuelOtpEmail = otpEmail;
                if (res.dev_code) {
                    window.__sportfuelOtp = String(res.dev_code);
                }

                otpInfo.textContent = 'Code envoyé à votre Gmail 📧 : ' + otpEmail;
                if (res.dev_code) {
                    otpInfo.textContent += ' (mode dev: ' + res.dev_code + ')';
                }
                otpInputs.forEach(function (input) { input.value = ''; });
                setForgotStep(2);
                showView(viewStep2);
                otpInputs[0].focus();
            })
            .catch(function () {
                alert('❌ Erreur serveur lors de l’envoi du code.');
            });
    };

    if (sendCodeBtn) {
        // Double événement géré dans HTML onclick="sendOtpCodeFlow(event)"
    }

    otpInputs.forEach(function (input, index) {
        input.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 1);
            if (this.value && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !this.value && index > 0) {
                otpInputs[index - 1].focus();
            }
        });
    });

    if (verifyOtpBtn) {
        window.verifyOtpFlowInternal = function () {
            const enteredCode = otpInputs.map(function (input) { return input.value; }).join('');
            if (enteredCode.length !== 4) {
                alert('❌ Entrez le code OTP complet (4 chiffres).');
                return;
            }
            fetch(API + '?action=verify_reset_code', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: otpEmail || window.__sportfuelOtpEmail || '', code: enteredCode })
            })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (!res.success) {
                        alert('❌ ' + (res.message || 'Code OTP incorrect.'));
                        return;
                    }
                    setForgotStep(3);
                    showView(viewStep3);
                    newPassword.focus();
                })
                .catch(function () {
                    alert('❌ Erreur serveur lors de la vérification OTP.');
                });
        };

        // Double événement géré dans HTML onclick="verifyOtpFlow(event)"
    }

    if (savePasswordBtn) {
        window.saveNewPasswordFlowInternal = function () {
            const password = newPassword.value;
            const confirmPassword = confirmNewPassword.value;

            resetFeedback.textContent = '';
            resetFeedback.className = 'reset-feedback';

            fetch(API + '?action=reset_password', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    email: otpEmail || window.__sportfuelOtpEmail || '',
                    password: password,
                    confirm_password: confirmPassword
                })
            })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (!res.success) {
                        console.error('Erreur API reset_password:', res);
                        resetFeedback.textContent = '❌ ' + (res.message || 'Erreur de réinitialisation.');
                        resetFeedback.classList.add('error');
                        return;
                    }

                    resetFeedback.textContent = '✅ ' + (res.message || 'Mot de passe mis à jour avec succès.');
                    resetFeedback.classList.add('success');
                    console.log('Mot de passe mis à jour avec succès.');
                    setTimeout(function () {
                        resetForgotFlow();
                    }, 1500);
                })
                .catch(function (err) {
                    console.error('Erreur Fetch reset_password:', err);
                    resetFeedback.textContent = '❌ Erreur serveur ou connexion lors de la réinitialisation.';
                    resetFeedback.classList.add('error');
                });
        };

        // Double événement géré dans HTML onclick="saveNewPasswordFlow(event)"
    }

    if (backToLogin1) {
        // Géré dans HTML
    }
    if (backToStep1) {
        window.goBackToStep1FlowInternal = function () {
            setForgotStep(1);
            showView(viewStep1);
            forgotEmail.focus();
        };

        // Géré dans HTML
    }
    // --- Face Recognition (Option B - AI) Implementation ---

    async function loadModels() {
        if (modelsLoaded) return true;
        try {
            scanMsg.textContent = "Chargement de l'IA (veuillez patienter)...";
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]);
            modelsLoaded = true;
            return true;
        } catch (e) {
            console.error("Erreur chargement modèles:", e);
            scanMsg.textContent = "❌ Erreur de chargement de l'IA.";
            return false;
        }
    }

    function checkFaceIDStatus() {
        const descriptor = localStorage.getItem(FACEID_KEY);
        if (descriptor) {
            faceIdBadge.innerHTML = 'Reconnaissance Faciale active 🟢 <a id="reconfigureFaceId">Réinitialiser</a>';
            faceIdBadge.className = 'faceid-badge active';
            document.getElementById('reconfigureFaceId').onclick = (e) => {
                e.preventDefault();
                if (confirm("Voulez-vous supprimer votre empreinte faciale ?")) {
                    localStorage.removeItem(FACEID_KEY);
                    checkFaceIDStatus();
                }
            };
        } else {
            faceIdBadge.innerHTML = 'Face ID non configuré 🔴 <a id="configureFaceId">Configurer</a>';
            faceIdBadge.className = 'faceid-badge inactive';
            document.getElementById('configureFaceId').onclick = (e) => {
                e.preventDefault();
                window.location.href = 'inscription.html';
            };
        }
    }

    async function startWebcam() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
            webcam.srcObject = stream;
            return true;
        } catch (e) {
            alert("❌ Impossible d'accéder à la caméra.");
            return false;
        }
    }

    function stopWebcam() {
        if (webcam.srcObject) {
            webcam.srcObject.getTracks().forEach(track => track.stop());
        }
    }

    async function startFaceIDLogin() {
        scanOverlay.style.display = 'flex';
        const ok = await loadModels();
        if (!ok) return;

        scanMsg.textContent = "Récupération des profils autorisés...";
        let dbDescriptors = [];
        try {
            const response = await fetch('../Controller/api.php?action=get_face_descriptors', {
                credentials: 'include'
            });
            const res = await response.json();
            if (res.success) {
                dbDescriptors = res.descriptors.map(d => ({
                    id: d.id,
                    nom: d.nom,
                    descriptor: new Float32Array(JSON.parse(d.face_descriptor))
                }));
            }
        } catch (e) {
            console.error("Erreur chargement descriptors DB:", e);
        }

        if (dbDescriptors.length === 0) {
            scanMsg.textContent = "❌ Aucun profil Face ID trouvé en base.";
            setTimeout(() => scanOverlay.style.display = 'none', 2000);
            return;
        }

        const cameraOk = await startWebcam();
        if (!cameraOk) {
            scanOverlay.style.display = 'none';
            return;
        }

        scanMsg.textContent = "Analyse de votre visage...";

        // Boucle de reconnaissance
        const interval = setInterval(async () => {
            const detection = await faceapi.detectSingleFace(webcam, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (detection) {
                let bestMatch = null;
                let minDistance = 1.0;

                // On compare avec tous les profils de la DB
                for (const profile of dbDescriptors) {
                    const distance = faceapi.euclideanDistance(detection.descriptor, profile.descriptor);
                    if (distance < minDistance) {
                        minDistance = distance;
                        bestMatch = profile;
                    }
                }

                if (bestMatch && minDistance < 0.45) { // Seuil strict pour la DB
                    clearInterval(interval);
                    scanMsg.textContent = `✅ Bonjour ${bestMatch.nom} !`;
                    
                    // --- NOUVEAU : Connexion via le serveur ---
                    try {
                        const loginRes = await fetch('../Controller/api.php?action=login_by_id', {
                            method: 'POST',
                            credentials: 'include',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: bestMatch.id })
                        });
                        const loginData = await loginRes.json();
                        
                        if (loginData.success) {
                            stopWebcam();
                            setTimeout(() => {
                                window.location.href = loginData.redirect;
                            }, 1000);
                        } else {
                            scanMsg.textContent = "❌ Erreur de connexion : " + loginData.message;
                            setTimeout(() => scanOverlay.style.display = 'none', 2000);
                        }
                    } catch (err) {
                        console.error("Erreur login_by_id:", err);
                    }
                } else {
                    scanMsg.textContent = "Visage détecté... (Vérification)";
                }
            } else {
                scanMsg.textContent = "Cherche un visage...";
            }
        }, 800);

        // Timeout après 15 secondes
        setTimeout(() => {
            if (scanOverlay.style.display !== 'none') {
                clearInterval(interval);
                stopWebcam();
                scanMsg.textContent = "❌ Délai dépassé. Réessayez.";
                setTimeout(() => scanOverlay.style.display = 'none', 2000);
            }
        }, 15000);
    }

    if (faceIdBtn) {
        faceIdBtn.addEventListener('click', startFaceIDLogin);
    }

    checkFaceIDStatus();

    resetForgotFlow();
});

/**
 * SportFuel — Copilote Séance
 * Local exercise database + workout generator + NLP parser + Web Speech API
 * No external API required — 100% browser-native
 */
(function () {
    'use strict';

    /* ================================================================
       1. EXERCISE DATABASE — 5 sport types du site SportFuel
       ================================================================ */
    const EXERCISE_DB = {
        musculation: [
            { nom: 'Squats', duree: 45, series: 4, reps: 12, niveau: 1, equip: ['aucun','haltères','barre'] },
            { nom: 'Fentes avant', duree: 40, series: 3, reps: 12, niveau: 1, equip: ['aucun','haltères'] },
            { nom: 'Développé couché', duree: 50, series: 4, reps: 10, niveau: 2, equip: ['barre','banc'] },
            { nom: 'Développé militaire', duree: 45, series: 4, reps: 10, niveau: 2, equip: ['barre','haltères'] },
            { nom: 'Rowing haltère', duree: 45, series: 4, reps: 10, niveau: 2, equip: ['haltères'] },
            { nom: 'Tractions', duree: 40, series: 4, reps: 8, niveau: 3, equip: ['barre de traction'] },
            { nom: 'Curl biceps haltères', duree: 35, series: 3, reps: 12, niveau: 1, equip: ['haltères'] },
            { nom: 'Extension triceps poulie', duree: 35, series: 3, reps: 12, niveau: 1, equip: ['machine'] },
            { nom: 'Pompes classiques', duree: 35, series: 3, reps: 15, niveau: 1, equip: ['aucun'] },
            { nom: 'Soulevé de terre roumain', duree: 50, series: 4, reps: 10, niveau: 2, equip: ['barre','haltères'] },
            { nom: 'Dips', duree: 35, series: 3, reps: 10, niveau: 2, equip: ['barres parallèles'] },
            { nom: 'Élévations latérales', duree: 30, series: 3, reps: 15, niveau: 1, equip: ['haltères'] },
            { nom: 'Leg press', duree: 50, series: 4, reps: 10, niveau: 2, equip: ['machine'] },
            { nom: 'Crunchs', duree: 25, series: 3, reps: 20, niveau: 1, equip: ['aucun'] },
            { nom: 'Planche', duree: 30, series: 3, reps: 1, niveau: 1, equip: ['aucun'] },
        ],
        cardio: [
            { nom: 'Burpees', duree: 30, series: 3, reps: 10, niveau: 2, equip: ['aucun'] },
            { nom: 'Jumping jacks', duree: 20, series: 3, reps: 30, niveau: 1, equip: ['aucun'] },
            { nom: 'Corde à sauter', duree: 30, series: 3, reps: 1, niveau: 1, equip: ['corde'] },
            { nom: 'Mountain climbers', duree: 30, series: 3, reps: 20, niveau: 1, equip: ['aucun'] },
            { nom: 'Box jumps', duree: 35, series: 3, reps: 10, niveau: 2, equip: ['box'] },
            { nom: 'Rameur', duree: 40, series: 1, reps: 1, niveau: 1, equip: ['machine'] },
            { nom: 'Vélo stationnaire', duree: 40, series: 1, reps: 1, niveau: 1, equip: ['machine'] },
            { nom: 'HIIT circuits', duree: 35, series: 4, reps: 1, niveau: 2, equip: ['aucun'] },
            { nom: 'Squat jumps', duree: 25, series: 3, reps: 15, niveau: 2, equip: ['aucun'] },
            { nom: 'High knees', duree: 20, series: 3, reps: 30, niveau: 1, equip: ['aucun'] },
        ],
        course: [
            { nom: 'Footing léger', duree: 60, series: 1, reps: 1, niveau: 1, equip: ['aucun'] },
            { nom: 'Course tempo', duree: 50, series: 1, reps: 1, niveau: 2, equip: ['aucun'] },
            { nom: 'Intervalles 400m', duree: 40, series: 6, reps: 1, niveau: 2, equip: ['aucun'] },
            { nom: 'Sprints 100m', duree: 25, series: 8, reps: 1, niveau: 3, equip: ['aucun'] },
            { nom: 'Fartlek', duree: 50, series: 1, reps: 1, niveau: 2, equip: ['aucun'] },
            { nom: 'Côtes / Montées', duree: 40, series: 5, reps: 1, niveau: 2, equip: ['aucun'] },
            { nom: 'Course longue distance', duree: 90, series: 1, reps: 1, niveau: 2, equip: ['aucun'] },
            { nom: 'Course de récupération', duree: 30, series: 1, reps: 1, niveau: 1, equip: ['aucun'] },
            { nom: 'Tapis de course', duree: 45, series: 1, reps: 1, niveau: 1, equip: ['machine'] },
            { nom: 'Foulées bondissantes', duree: 20, series: 4, reps: 10, niveau: 2, equip: ['aucun'] },
        ],
        natation: [
            { nom: 'Crawl', duree: 50, series: 4, reps: 1, niveau: 1, equip: ['aucun'] },
            { nom: 'Dos crawlé', duree: 45, series: 4, reps: 1, niveau: 1, equip: ['aucun'] },
            { nom: 'Brasse', duree: 45, series: 4, reps: 1, niveau: 1, equip: ['aucun'] },
            { nom: 'Papillon', duree: 40, series: 3, reps: 1, niveau: 3, equip: ['aucun'] },
            { nom: 'Nage libre endurance', duree: 60, series: 1, reps: 1, niveau: 1, equip: ['aucun'] },
            { nom: 'Battements de jambes (planche)', duree: 30, series: 4, reps: 1, niveau: 1, equip: ['planche'] },
            { nom: 'Pull buoy — bras seuls', duree: 35, series: 4, reps: 1, niveau: 2, equip: ['pull buoy'] },
            { nom: 'Sprints 50m', duree: 25, series: 6, reps: 1, niveau: 2, equip: ['aucun'] },
            { nom: 'Nage avec palmes', duree: 40, series: 3, reps: 1, niveau: 1, equip: ['palmes'] },
            { nom: 'Aquagym', duree: 45, series: 1, reps: 1, niveau: 1, equip: ['aucun'] },
        ],
        yoga: [
            { nom: 'Salutation au soleil', duree: 30, series: 3, reps: 1, niveau: 1, equip: ['tapis'] },
            { nom: 'Guerrier I (Virabhadrasana)', duree: 25, series: 2, reps: 1, niveau: 1, equip: ['tapis'] },
            { nom: 'Guerrier II', duree: 25, series: 2, reps: 1, niveau: 1, equip: ['tapis'] },
            { nom: 'Posture de l\'arbre', duree: 20, series: 2, reps: 1, niveau: 1, equip: ['tapis'] },
            { nom: 'Chien tête en bas', duree: 20, series: 3, reps: 1, niveau: 1, equip: ['tapis'] },
            { nom: 'Posture du cobra', duree: 20, series: 3, reps: 1, niveau: 1, equip: ['tapis'] },
            { nom: 'Torsion assise', duree: 20, series: 2, reps: 1, niveau: 1, equip: ['tapis'] },
            { nom: 'Posture du pigeon', duree: 25, series: 2, reps: 1, niveau: 2, equip: ['tapis'] },
            { nom: 'Relaxation Savasana', duree: 30, series: 1, reps: 1, niveau: 1, equip: ['tapis'] },
            { nom: 'Respiration Pranayama', duree: 20, series: 3, reps: 1, niveau: 1, equip: ['aucun'] },
        ]
    };

    // Niveau mapping: 1=débutant, 2=intermédiaire, 3=avancé
    const NIVEAU_MAP = { 'débutant': 1, 'intermédiaire': 2, 'avancé': 3 };
    const NIVEAU_LABELS = { 1: 'Débutant', 2: 'Intermédiaire', 3: 'Avancé' };

    // Objectif → rep/series/rest adjustments
    const OBJECTIF_CONFIG = {
        force:        { seriesMult: 1.2, repsMult: 0.6, groups: ['musculation'] },
        hypertrophie: { seriesMult: 1.0, repsMult: 1.0, groups: ['musculation'] },
        endurance:    { seriesMult: 0.8, repsMult: 1.5, groups: ['cardio','course','natation'] },
        perte_poids:  { seriesMult: 0.8, repsMult: 1.3, groups: ['cardio','course','musculation'] },
        souplesse:    { seriesMult: 1.0, repsMult: 1.0, groups: ['yoga'] },
    };

    /* ================================================================
       2. WORKOUT GENERATOR
       ================================================================ */
    function generateWorkout(objectif, niveau, dureeMins, equipList) {
        const config = OBJECTIF_CONFIG[objectif] || OBJECTIF_CONFIG.hypertrophie;
        const niveauNum = NIVEAU_MAP[niveau] || 2;
        const targetDuration = dureeMins * 60; // seconds

        // Collect eligible exercises
        let pool = [];
        config.groups.forEach(function (group) {
            (EXERCISE_DB[group] || []).forEach(function (ex) {
                if (ex.niveau > niveauNum) return;
                if (equipList.length > 0 && !ex.equip.some(e => equipList.includes(e) || e === 'aucun')) return;
                pool.push({ ...ex, groupe: group });
            });
        });

        // Shuffle
        pool.sort(() => Math.random() - 0.5);

        // Pick exercises until we fill the duration
        const picked = [];
        let totalDuration = 0;
        const usedGroups = new Set();

        // Prioritize variety across groups
        for (const ex of pool) {
            if (totalDuration >= targetDuration) break;
            if (picked.length >= 8) break; // max 8 exercises
            // Try to get different groups first
            if (usedGroups.has(ex.groupe) && pool.filter(e => !usedGroups.has(e.groupe)).length > 0) continue;

            const adjustedSeries = Math.max(2, Math.round(ex.series * config.seriesMult));
            const adjustedReps = Math.max(1, Math.round(ex.reps * config.repsMult));
            const exDuration = ex.duree * config.seriesMult;

            picked.push({
                nom: ex.nom,
                groupe: ex.groupe,
                duree: Math.round(exDuration),
                series: adjustedSeries,
                reps: adjustedReps,
                equip: ex.equip[0] || 'aucun'
            });

            totalDuration += exDuration;
            usedGroups.add(ex.groupe);
        }

        // If we need more, fill from remaining pool
        if (totalDuration < targetDuration * 0.7) {
            for (const ex of pool) {
                if (totalDuration >= targetDuration) break;
                if (picked.length >= 8) break;
                if (picked.some(p => p.nom === ex.nom)) continue;

                const adjustedSeries = Math.max(2, Math.round(ex.series * config.seriesMult));
                const adjustedReps = Math.max(1, Math.round(ex.reps * config.repsMult));
                picked.push({
                    nom: ex.nom,
                    groupe: ex.groupe,
                    duree: Math.round(ex.duree * config.seriesMult),
                    series: adjustedSeries,
                    reps: adjustedReps,
                    equip: ex.equip[0] || 'aucun'
                });
                totalDuration += ex.duree * config.seriesMult;
            }
        }

        return picked;
    }

    /* ================================================================
       3. NATURAL LANGUAGE PARSER
       ================================================================ */
    const GROUP_KEYWORDS = {
        musculation: ['musculation','muscu','muscle','pompes','squat','développé','rowing','traction','curl','biceps','triceps','haltères','barre','jambes','poitrine','dos','épaules','bras','abdos','force','poids'],
        cardio:      ['cardio','hiit','vélo','velo','rameur','burpees','jumping','corde','interval','circuit'],
        course:      ['course','courir','running','footing','sprint','marathon','jogging','tempo','fartlek','tapis'],
        natation:    ['natation','nager','nage','piscine','crawl','brasse','papillon','aqua','swim','palmes'],
        yoga:        ['yoga','stretching','souplesse','posture','méditation','meditation','guerrier','salutation','respiration','relaxation','pilates','étirement'],
    };

    const EQUIP_KEYWORDS = {
        'haltères':  ['haltères','haltere','haltères','dumbell','dumbbell'],
        'barre':     ['barre','barbell','barre olympique'],
        'machine':   ['machine','machines','poulie','cable'],
        'élastique': ['élastique','elastique','bande','band','resistance'],
        'kettlebell':['kettlebell','kettle'],
        'banc':      ['banc','bench'],
        'aucun':     ['aucun','rien','poids du corps','bodyweight','corps','maison','home'],
    };

    const OBJECTIF_KEYWORDS = {
        force:        ['force','strength','fort','puissance','power','lourd'],
        hypertrophie: ['masse','volume','hypertrophie','gros'],
        endurance:    ['endurance','stamina','longue','souffle'],
        perte_poids:  ['perte','maigrir','mince','brûler','bruler','calories','sèche','seche','fat','lean'],
        souplesse:    ['souplesse','flexibilité','étirement','stretching','zen','détente','relaxation'],
    };

    function parseNaturalLanguage(text) {
        const lower = text.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        const lowerOriginal = text.toLowerCase();

        // Extract duration
        let duree = 45; // default
        const durationMatch = lowerOriginal.match(/(\d+)\s*min/);
        if (durationMatch) duree = parseInt(durationMatch[1], 10);

        // Extract groups
        const groups = [];
        for (const [group, keywords] of Object.entries(GROUP_KEYWORDS)) {
            for (const kw of keywords) {
                if (lowerOriginal.includes(kw)) { groups.push(group); break; }
            }
        }

        // Extract equipment
        const equip = [];
        for (const [eq, keywords] of Object.entries(EQUIP_KEYWORDS)) {
            for (const kw of keywords) {
                if (lowerOriginal.includes(kw)) { equip.push(eq); break; }
            }
        }

        // Extract objectif
        let objectif = 'hypertrophie';
        for (const [obj, keywords] of Object.entries(OBJECTIF_KEYWORDS)) {
            for (const kw of keywords) {
                if (lowerOriginal.includes(kw)) { objectif = obj; break; }
            }
        }

        // Extract niveau
        let niveau = 'intermédiaire';
        if (lowerOriginal.includes('débutant') || lowerOriginal.includes('debutant') || lowerOriginal.includes('facile')) niveau = 'débutant';
        if (lowerOriginal.includes('avancé') || lowerOriginal.includes('avance') || lowerOriginal.includes('difficile') || lowerOriginal.includes('intense')) niveau = 'avancé';

        // If no groups detected, use objectif defaults
        const finalGroups = groups.length > 0 ? groups : (OBJECTIF_CONFIG[objectif]?.groups || ['jambes','poitrine','dos']);

        // Override OBJECTIF_CONFIG groups with detected ones
        const customConfig = { ...OBJECTIF_CONFIG[objectif], groups: finalGroups };
        const oldConfig = OBJECTIF_CONFIG[objectif];
        OBJECTIF_CONFIG[objectif] = customConfig;
        const result = generateWorkout(objectif, niveau, duree, equip);
        OBJECTIF_CONFIG[objectif] = oldConfig;

        return {
            exercises: result,
            parsed: { duree, groups: finalGroups, equip, objectif, niveau },
        };
    }

    /* ================================================================
       4. WEB SPEECH API
       ================================================================ */
    class SpeechHelper {
        constructor() {
            const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.supported = !!SR;
            if (!this.supported) return;
            this.recognition = new SR();
            this.recognition.lang = 'fr-FR';
            this.recognition.continuous = false;
            this.recognition.interimResults = true;
            this.listening = false;
        }

        start(onResult, onEnd, onError) {
            if (!this.supported) { onError?.('Speech API non supportée'); return; }
            this.recognition.onresult = function (e) {
                let transcript = '';
                for (let i = 0; i < e.results.length; i++) {
                    transcript += e.results[i][0].transcript;
                }
                onResult?.(transcript, e.results[e.results.length - 1].isFinal);
            };
            this.recognition.onend = () => { this.listening = false; onEnd?.(); };
            this.recognition.onerror = (e) => { this.listening = false; onError?.(e.error); };
            this.listening = true;
            this.recognition.start();
        }

        stop() {
            if (this.listening) this.recognition.stop();
            this.listening = false;
        }

        toggle(onResult, onEnd, onError) {
            if (this.listening) this.stop();
            else this.start(onResult, onEnd, onError);
        }
    }

    const speech = new SpeechHelper();

    /* ================================================================
       5. UI RENDERING
       ================================================================ */
    const GROUP_ICONS = {
        musculation: '🏋️', cardio: '🏃', course: '🏃‍♂️',
        natation: '🏊', yoga: '🧘'
    };

    const GROUP_COLORS = {
        musculation: '#f4845f', cardio: '#14b8a6', course: '#52b788',
        natation: '#6ec6ff', yoga: '#8338ec'
    };

    function renderExerciseCards(exercises, containerId) {
        const container = document.getElementById(containerId);
        if (!exercises.length) {
            container.innerHTML = '<p class="copilot-empty">Aucun exercice trouvé. Essayez d\'autres paramètres.</p>';
            return;
        }

        const totalDuration = exercises.reduce((s, e) => s + e.duree, 0);

        let html = '<div class="copilot-summary"><span>🏋️ ' + exercises.length + ' exercices</span><span>⏱️ ~' + Math.round(totalDuration / 60) + ' min</span></div>';
        html += '<div class="copilot-cards">';
        exercises.forEach(function (ex, i) {
            const color = GROUP_COLORS[ex.groupe] || '#52b788';
            const icon = GROUP_ICONS[ex.groupe] || '🏋️';
            html += '<div class="copilot-ex-card" data-index="' + i + '" style="--card-color:' + color + '">' +
                '<div class="copilot-ex-bar"></div>' +
                '<div class="copilot-ex-body">' +
                    '<div class="copilot-ex-header">' +
                        '<span class="copilot-ex-icon">' + icon + '</span>' +
                        '<div class="copilot-ex-title">' +
                            '<strong>' + ex.nom + '</strong>' +
                            '<span class="copilot-ex-group">' + ex.groupe + '</span>' +
                        '</div>' +
                        '<button type="button" class="copilot-ex-remove" onclick="window._copilotRemove(' + i + ')" title="Retirer">✕</button>' +
                    '</div>' +
                    '<div class="copilot-ex-stats">' +
                        '<div class="copilot-ex-stat"><span class="copilot-ex-stat-val">' + ex.series + '</span><span class="copilot-ex-stat-lbl">Séries</span></div>' +
                        '<div class="copilot-ex-stat"><span class="copilot-ex-stat-val">' + ex.reps + '</span><span class="copilot-ex-stat-lbl">Reps</span></div>' +
                        '<div class="copilot-ex-stat"><span class="copilot-ex-stat-val">' + ex.duree + 's</span><span class="copilot-ex-stat-lbl">Durée</span></div>' +
                        '<div class="copilot-ex-stat"><span class="copilot-ex-stat-val">' + (ex.equip || '—') + '</span><span class="copilot-ex-stat-lbl">Équip.</span></div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        });
        html += '</div>';
        html += '<button type="button" class="copilot-save-btn" id="copilotSaveBtn" onclick="window._copilotSave()">✅ Enregistrer cette séance</button>';

        container.innerHTML = html;
    }

    /* ================================================================
       6. SAVE WORKOUT
       ================================================================ */
    async function saveGeneratedWorkout(exercises, titre) {
        if (!exercises.length) return;
        const saveBtn = document.getElementById('copilotSaveBtn');
        if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = 'Enregistrement...'; }

        try {
            const today = new Date().toISOString().slice(0, 10);
            const totalDuration = Math.round(exercises.reduce((s, e) => s + e.duree, 0) / 60);
            const result = await createEntrainement(
                titre || 'Séance Copilote',
                today,
                totalDuration,
                'Générée par le Copilote SportFuel',
                totalDuration * 7
            );

            if (result && result.id_entrainement) {
                // Save individual exercises
                for (let i = 0; i < exercises.length; i++) {
                    const ex = exercises[i];
                    await createExercice(
                        result.id_entrainement,
                        ex.nom,
                        ex.duree,
                        ex.reps,
                        null,
                        ex.groupe + ' | ' + ex.series + ' séries',
                        i + 1
                    );
                }
            }

            const msgEl = document.getElementById('messageContainer');
            if (msgEl) msgEl.innerHTML = '<div class="alert alert-success"><strong>✅ Succès :</strong> Votre séance de ' + exercises.length + ' exercices a été enregistrée !</div>';

        } catch (e) {
            const msgEl = document.getElementById('messageContainer');
            if (msgEl) msgEl.innerHTML = '<div class="alert alert-danger"><strong>Erreur :</strong> ' + (e.message || 'Impossible d\'enregistrer') + '</div>';
        } finally {
            if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = '✅ Enregistrer cette séance'; }
        }
    }

    /* ================================================================
       7. UI EVENT WIRING
       ================================================================ */
    let currentExercises = [];

    window._copilotRemove = function (index) {
        currentExercises.splice(index, 1);
        renderExerciseCards(currentExercises, 'copilotResults');
    };

    window._copilotSave = function () {
        const obj = document.getElementById('copilotObjectif');
        const titre = obj ? obj.options[obj.selectedIndex].text : 'Séance Copilote';
        saveGeneratedWorkout(currentExercises, titre);
    };

    window._copilotGenerate = function () {
        const objectif = document.getElementById('copilotObjectif').value;
        const niveau = document.getElementById('copilotNiveau').value;
        const duree = parseInt(document.getElementById('copilotDuree').value, 10);

        const equipChecks = document.querySelectorAll('.copilot-equip-check:checked');
        const equip = Array.from(equipChecks).map(c => c.value);

        currentExercises = generateWorkout(objectif, niveau, duree, equip);
        renderExerciseCards(currentExercises, 'copilotResults');

        document.getElementById('copilotResults').scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    window._copilotParse = function () {
        const text = document.getElementById('copilotNlpInput').value.trim();
        if (!text) return;
        const result = parseNaturalLanguage(text);
        currentExercises = result.exercises;

        // Show what was understood
        const p = result.parsed;
        const feedbackHtml = '<div class="copilot-parsed-feedback">' +
            '<strong>🧠 Compris :</strong> ' +
            (p.groups.join(', ')) + ' • ' +
            p.duree + ' min • ' +
            p.niveau + ' • ' +
            p.objectif +
            (p.equip.length ? ' • Équip: ' + p.equip.join(', ') : '') +
        '</div>';

        document.getElementById('copilotResults').innerHTML = feedbackHtml;
        renderExerciseCards(currentExercises, 'copilotResults');
    };

    // Tab switching
    window._copilotTab = function (tab) {
        document.querySelectorAll('.copilot-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.copilot-panel').forEach(p => p.classList.remove('active'));
        document.querySelector('[data-tab="' + tab + '"]').classList.add('active');
        document.getElementById('copilotPanel_' + tab).classList.add('active');
        document.getElementById('copilotResults').innerHTML = '';
    };

    // Speech
    window._copilotMic = function () {
        const btn = document.getElementById('copilotMicBtn');
        const input = document.getElementById('copilotNlpInput');
        if (!speech.supported) {
            alert('La reconnaissance vocale n\'est pas supportée par votre navigateur. Utilisez Chrome ou Edge.');
            return;
        }
        speech.toggle(
            function (transcript, isFinal) {
                input.value = transcript;
                if (isFinal) btn.classList.remove('mic-active');
            },
            function () { btn.classList.remove('mic-active'); },
            function (err) { btn.classList.remove('mic-active'); console.warn('Speech error:', err); }
        );
        btn.classList.toggle('mic-active', speech.listening);
    };

})();

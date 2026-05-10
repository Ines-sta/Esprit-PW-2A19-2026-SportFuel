/**
 * SportFuel - Copilote Seance
 * Local exercise database + workout generator + NLP parser + Web Speech API
 * No external API required - 100% browser-native
 */
(function () {
    'use strict';

    function normalizeSavedTitle(rawTitle) {
        const source = String(rawTitle || 'Seance Copilote').trim();
        // Remove leading replacement chars and symbols (emoji/decorative prefixes).
        const withoutPrefix = source
            .replace(/^[?\s]+/, '')
            .replace(/^[^\p{L}\p{N}]+/u, '')
            .trim();
        return withoutPrefix || 'Seance Copilote';
    }

    const EXERCISE_DB = {
        musculation: [
            { nom: 'Squats', duree: 45, series: 4, reps: 12, niveau: 1, equip: ['aucun', 'halteres', 'barre'] },
            { nom: 'Fentes avant', duree: 40, series: 3, reps: 12, niveau: 1, equip: ['aucun', 'halteres'] },
            { nom: 'Developpe couche', duree: 50, series: 4, reps: 10, niveau: 2, equip: ['barre', 'banc'] },
            { nom: 'Developpe militaire', duree: 45, series: 4, reps: 10, niveau: 2, equip: ['barre', 'halteres'] },
            { nom: 'Rowing haltere', duree: 45, series: 4, reps: 10, niveau: 2, equip: ['halteres'] },
            { nom: 'Tractions', duree: 40, series: 4, reps: 8, niveau: 3, equip: ['barre de traction'] },
            { nom: 'Curl biceps halteres', duree: 35, series: 3, reps: 12, niveau: 1, equip: ['halteres'] },
            { nom: 'Extension triceps poulie', duree: 35, series: 3, reps: 12, niveau: 1, equip: ['machine'] },
            { nom: 'Pompes classiques', duree: 35, series: 3, reps: 15, niveau: 1, equip: ['aucun'] },
            { nom: 'Souleve de terre roumain', duree: 50, series: 4, reps: 10, niveau: 2, equip: ['barre', 'halteres'] },
            { nom: 'Dips', duree: 35, series: 3, reps: 10, niveau: 2, equip: ['barres paralleles'] },
            { nom: 'Elevations laterales', duree: 30, series: 3, reps: 15, niveau: 1, equip: ['halteres'] },
            { nom: 'Leg press', duree: 50, series: 4, reps: 10, niveau: 2, equip: ['machine'] },
            { nom: 'Crunchs', duree: 25, series: 3, reps: 20, niveau: 1, equip: ['aucun'] },
            { nom: 'Planche', duree: 30, series: 3, reps: 1, niveau: 1, equip: ['aucun'] },
        ],
        cardio: [
            { nom: 'Burpees', duree: 30, series: 3, reps: 10, niveau: 2, equip: ['aucun'] },
            { nom: 'Jumping jacks', duree: 20, series: 3, reps: 30, niveau: 1, equip: ['aucun'] },
            { nom: 'Corde a sauter', duree: 30, series: 3, reps: 1, niveau: 1, equip: ['corde'] },
            { nom: 'Mountain climbers', duree: 30, series: 3, reps: 20, niveau: 1, equip: ['aucun'] },
            { nom: 'Box jumps', duree: 35, series: 3, reps: 10, niveau: 2, equip: ['box'] },
            { nom: 'Rameur', duree: 40, series: 1, reps: 1, niveau: 1, equip: ['machine'] },
            { nom: 'Velo stationnaire', duree: 40, series: 1, reps: 1, niveau: 1, equip: ['machine'] },
            { nom: 'HIIT circuits', duree: 35, series: 4, reps: 1, niveau: 2, equip: ['aucun'] },
            { nom: 'Squat jumps', duree: 25, series: 3, reps: 15, niveau: 2, equip: ['aucun'] },
            { nom: 'High knees', duree: 20, series: 3, reps: 30, niveau: 1, equip: ['aucun'] },
        ],
        course: [
            { nom: 'Footing leger', duree: 60, series: 1, reps: 1, niveau: 1, equip: ['aucun'] },
            { nom: 'Course tempo', duree: 50, series: 1, reps: 1, niveau: 2, equip: ['aucun'] },
            { nom: 'Intervalles 400m', duree: 40, series: 6, reps: 1, niveau: 2, equip: ['aucun'] },
            { nom: 'Sprints 100m', duree: 25, series: 8, reps: 1, niveau: 3, equip: ['aucun'] },
            { nom: 'Fartlek', duree: 50, series: 1, reps: 1, niveau: 2, equip: ['aucun'] },
            { nom: 'Cotes / Montees', duree: 40, series: 5, reps: 1, niveau: 2, equip: ['aucun'] },
            { nom: 'Course longue distance', duree: 90, series: 1, reps: 1, niveau: 2, equip: ['aucun'] },
            { nom: 'Course de recuperation', duree: 30, series: 1, reps: 1, niveau: 1, equip: ['aucun'] },
            { nom: 'Tapis de course', duree: 45, series: 1, reps: 1, niveau: 1, equip: ['machine'] },
            { nom: 'Foulees bondissantes', duree: 20, series: 4, reps: 10, niveau: 2, equip: ['aucun'] },
        ],
        natation: [
            { nom: 'Crawl', duree: 50, series: 4, reps: 1, niveau: 1, equip: ['aucun'] },
            { nom: 'Dos crawle', duree: 45, series: 4, reps: 1, niveau: 1, equip: ['aucun'] },
            { nom: 'Brasse', duree: 45, series: 4, reps: 1, niveau: 1, equip: ['aucun'] },
            { nom: 'Papillon', duree: 40, series: 3, reps: 1, niveau: 3, equip: ['aucun'] },
            { nom: 'Nage libre endurance', duree: 60, series: 1, reps: 1, niveau: 1, equip: ['aucun'] },
            { nom: 'Battements de jambes (planche)', duree: 30, series: 4, reps: 1, niveau: 1, equip: ['planche'] },
            { nom: 'Pull buoy - bras seuls', duree: 35, series: 4, reps: 1, niveau: 2, equip: ['pull buoy'] },
            { nom: 'Sprints 50m', duree: 25, series: 6, reps: 1, niveau: 2, equip: ['aucun'] },
            { nom: 'Nage avec palmes', duree: 40, series: 3, reps: 1, niveau: 1, equip: ['palmes'] },
            { nom: 'Aquagym', duree: 45, series: 1, reps: 1, niveau: 1, equip: ['aucun'] },
        ],
        yoga: [
            { nom: 'Salutation au soleil', duree: 30, series: 3, reps: 1, niveau: 1, equip: ['tapis'] },
            { nom: 'Guerrier I (Virabhadrasana)', duree: 25, series: 2, reps: 1, niveau: 1, equip: ['tapis'] },
            { nom: 'Guerrier II', duree: 25, series: 2, reps: 1, niveau: 1, equip: ['tapis'] },
            { nom: "Posture de l'arbre", duree: 20, series: 2, reps: 1, niveau: 1, equip: ['tapis'] },
            { nom: 'Chien tete en bas', duree: 20, series: 3, reps: 1, niveau: 1, equip: ['tapis'] },
            { nom: 'Posture du cobra', duree: 20, series: 3, reps: 1, niveau: 1, equip: ['tapis'] },
            { nom: 'Torsion assise', duree: 20, series: 2, reps: 1, niveau: 1, equip: ['tapis'] },
            { nom: 'Posture du pigeon', duree: 25, series: 2, reps: 1, niveau: 2, equip: ['tapis'] },
            { nom: 'Relaxation Savasana', duree: 30, series: 1, reps: 1, niveau: 1, equip: ['tapis'] },
            { nom: 'Respiration Pranayama', duree: 20, series: 3, reps: 1, niveau: 1, equip: ['aucun'] },
        ]
    };

    const NIVEAU_MAP = { 'debutant': 1, 'intermediaire': 2, 'avance': 3 };

    const OBJECTIF_CONFIG = {
        force:        { seriesMult: 1.2, repsMult: 0.6, groups: ['musculation'] },
        hypertrophie: { seriesMult: 1.0, repsMult: 1.0, groups: ['musculation'] },
        endurance:    { seriesMult: 0.8, repsMult: 1.5, groups: ['cardio', 'course', 'natation'] },
        perte_poids:  { seriesMult: 0.8, repsMult: 1.3, groups: ['cardio', 'course', 'musculation'] },
        souplesse:    { seriesMult: 1.0, repsMult: 1.0, groups: ['yoga'] },
    };

    function normalizeText(s) {
        return String(s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    function normalizeEquipValue(v) {
        const key = normalizeText(v);
        if (key === 'halteres' || key === 'halteres') return 'halteres';
        if (key === 'elastique') return 'elastique';
        return key;
    }

    function generateWorkout(objectif, niveau, dureeMins, equipList) {
        const config = OBJECTIF_CONFIG[objectif] || OBJECTIF_CONFIG.hypertrophie;
        const niveauNum = NIVEAU_MAP[normalizeText(niveau)] || 2;
        const targetDuration = dureeMins * 60;

        let pool = [];
        const equipNormalized = (equipList || []).map(normalizeEquipValue);

        config.groups.forEach(function (group) {
            (EXERCISE_DB[group] || []).forEach(function (ex) {
                if (ex.niveau > niveauNum) return;
                if (equipNormalized.length > 0) {
                    const exEquip = ex.equip.map(normalizeEquipValue);
                    const hasMatch = exEquip.some(function (e) {
                        return equipNormalized.includes(e) || e === 'aucun';
                    });
                    if (!hasMatch) return;
                }
                pool.push(Object.assign({}, ex, { groupe: group }));
            });
        });

        pool.sort(function () { return Math.random() - 0.5; });

        const picked = [];
        let totalDuration = 0;
        const usedGroups = new Set();

        for (const ex of pool) {
            if (totalDuration >= targetDuration) break;
            if (picked.length >= 8) break;
            if (usedGroups.has(ex.groupe) && pool.filter(function (e) { return !usedGroups.has(e.groupe); }).length > 0) continue;

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

        if (totalDuration < targetDuration * 0.7) {
            for (const ex of pool) {
                if (totalDuration >= targetDuration) break;
                if (picked.length >= 8) break;
                if (picked.some(function (p) { return p.nom === ex.nom; })) continue;

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

    const GROUP_KEYWORDS = {
        musculation: ['musculation', 'muscu', 'muscle', 'pompes', 'squat', 'developpe', 'rowing', 'traction', 'curl', 'biceps', 'triceps', 'halteres', 'barre', 'jambes', 'poitrine', 'dos', 'epaules', 'bras', 'abdos', 'force', 'poids'],
        cardio:      ['cardio', 'hiit', 'velo', 'rameur', 'burpees', 'jumping', 'corde', 'interval', 'circuit'],
        course:      ['course', 'courir', 'running', 'footing', 'sprint', 'marathon', 'jogging', 'tempo', 'fartlek', 'tapis'],
        natation:    ['natation', 'nager', 'nage', 'piscine', 'crawl', 'brasse', 'papillon', 'aqua', 'swim', 'palmes'],
        yoga:        ['yoga', 'stretching', 'souplesse', 'posture', 'meditation', 'guerrier', 'salutation', 'respiration', 'relaxation', 'pilates', 'etirement'],
    };

    const EQUIP_KEYWORDS = {
        'halteres':  ['halteres', 'dumbell', 'dumbbell'],
        'barre':     ['barre', 'barbell', 'barre olympique'],
        'machine':   ['machine', 'machines', 'poulie', 'cable'],
        'elastique': ['elastique', 'bande', 'band', 'resistance'],
        'kettlebell':['kettlebell', 'kettle'],
        'banc':      ['banc', 'bench'],
        'aucun':     ['aucun', 'rien', 'poids du corps', 'bodyweight', 'corps', 'maison', 'home'],
    };

    const OBJECTIF_KEYWORDS = {
        force:        ['force', 'strength', 'fort', 'puissance', 'power', 'lourd'],
        hypertrophie: ['masse', 'volume', 'hypertrophie', 'gros'],
        endurance:    ['endurance', 'stamina', 'longue', 'souffle'],
        perte_poids:  ['perte', 'maigrir', 'mince', 'bruler', 'calories', 'seche', 'fat', 'lean'],
        souplesse:    ['souplesse', 'flexibilite', 'etirement', 'stretching', 'zen', 'detente', 'relaxation'],
    };

    function parseNaturalLanguage(text) {
        const lower = normalizeText(text);

        let duree = 45;
        const durationMatch = lower.match(/(\d+)\s*min/);
        if (durationMatch) duree = parseInt(durationMatch[1], 10);

        const groups = [];
        Object.entries(GROUP_KEYWORDS).forEach(function (entry) {
            const group = entry[0];
            const keywords = entry[1];
            for (const kw of keywords) {
                if (lower.includes(kw)) { groups.push(group); break; }
            }
        });

        const equip = [];
        Object.entries(EQUIP_KEYWORDS).forEach(function (entry) {
            const eq = entry[0];
            const keywords = entry[1];
            for (const kw of keywords) {
                if (lower.includes(kw)) { equip.push(eq); break; }
            }
        });

        let objectif = 'hypertrophie';
        Object.entries(OBJECTIF_KEYWORDS).forEach(function (entry) {
            const obj = entry[0];
            const keywords = entry[1];
            for (const kw of keywords) {
                if (lower.includes(kw)) { objectif = obj; break; }
            }
        });

        let niveau = 'intermediaire';
        if (lower.includes('debutant') || lower.includes('facile')) niveau = 'debutant';
        if (lower.includes('avance') || lower.includes('difficile') || lower.includes('intense')) niveau = 'avance';

        const finalGroups = groups.length > 0 ? groups : ((OBJECTIF_CONFIG[objectif] && OBJECTIF_CONFIG[objectif].groups) || ['musculation']);

        const customConfig = Object.assign({}, OBJECTIF_CONFIG[objectif], { groups: finalGroups });
        const oldConfig = OBJECTIF_CONFIG[objectif];
        OBJECTIF_CONFIG[objectif] = customConfig;
        const result = generateWorkout(objectif, niveau, duree, equip);
        OBJECTIF_CONFIG[objectif] = oldConfig;

        return {
            exercises: result,
            parsed: { duree: duree, groups: finalGroups, equip: equip, objectif: objectif, niveau: niveau },
        };
    }

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
            if (!this.supported) { if (onError) onError('Speech API non supportee'); return; }
            this.recognition.onresult = function (e) {
                let transcript = '';
                for (let i = 0; i < e.results.length; i++) {
                    transcript += e.results[i][0].transcript;
                }
                if (onResult) onResult(transcript, e.results[e.results.length - 1].isFinal);
            };
            this.recognition.onend = () => { this.listening = false; if (onEnd) onEnd(); };
            this.recognition.onerror = (e) => { this.listening = false; if (onError) onError(e.error); };
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

    const GROUP_ICONS = {
        musculation: '🏋️', cardio: '🏃', course: '🏃‍♂️', natation: '🏊', yoga: '🧘'
    };

    const GROUP_COLORS = {
        musculation: '#f4845f', cardio: '#14b8a6', course: '#52b788', natation: '#6ec6ff', yoga: '#8338ec'
    };

    function renderExerciseCards(exercises, containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        if (!exercises.length) {
            container.innerHTML = '<p class="copilot-empty">Aucun exercice trouve. Essayez d\'autres parametres.</p>';
            return;
        }

        const totalDuration = exercises.reduce(function (s, e) { return s + e.duree; }, 0);

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
                        '<div class="copilot-ex-stat"><span class="copilot-ex-stat-val">' + ex.series + '</span><span class="copilot-ex-stat-lbl">Series</span></div>' +
                        '<div class="copilot-ex-stat"><span class="copilot-ex-stat-val">' + ex.reps + '</span><span class="copilot-ex-stat-lbl">Reps</span></div>' +
                        '<div class="copilot-ex-stat"><span class="copilot-ex-stat-val">' + ex.duree + 's</span><span class="copilot-ex-stat-lbl">Duree</span></div>' +
                        '<div class="copilot-ex-stat"><span class="copilot-ex-stat-val">' + (ex.equip || '—') + '</span><span class="copilot-ex-stat-lbl">Equip.</span></div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        });
        html += '</div>';
        html += '<button type="button" class="copilot-save-btn" id="copilotSaveBtn" onclick="window._copilotSave()">✅ Enregistrer cette seance</button>';

        container.innerHTML = html;
    }

    async function saveGeneratedWorkout(exercises, titre) {
        if (!exercises.length) return;
        const saveBtn = document.getElementById('copilotSaveBtn');
        if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = 'Enregistrement...'; }

        try {
            const today = new Date().toISOString().slice(0, 10);
            const totalDuration = Math.round(exercises.reduce(function (s, e) { return s + e.duree; }, 0) / 60);
            const result = await createEntrainement(
                titre || 'Seance Copilote',
                today,
                totalDuration,
                'Generee par le Copilote SportFuel',
                totalDuration * 7
            );

            if (result && result.id_entrainement) {
                for (let i = 0; i < exercises.length; i++) {
                    const ex = exercises[i];
                    await addExerciceSeance({
                        id_entrainement: result.id_entrainement,
                        nom_exercice: ex.nom,
                        duree_secondes: ex.duree,
                        series: ex.series,
                        repetitions: ex.reps,
                        charge_kg: '',
                        distance_km: ''
                    });
                }
            }

            const msgEl = document.getElementById('messageContainer');
            if (msgEl) msgEl.innerHTML = '<div class="alert alert-success"><strong>✅ Succes :</strong> Votre seance de ' + exercises.length + ' exercices a ete enregistree !</div>';

        } catch (e) {
            const msgEl = document.getElementById('messageContainer');
            if (msgEl) msgEl.innerHTML = '<div class="alert alert-danger"><strong>Erreur :</strong> ' + (e.message || 'Impossible d\'enregistrer') + '</div>';
        } finally {
            if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = '✅ Enregistrer cette seance'; }
        }
    }

    let currentExercises = [];

    window._copilotRemove = function (index) {
        currentExercises.splice(index, 1);
        renderExerciseCards(currentExercises, 'copilotResults');
    };

    window._copilotSave = function () {
        const obj = document.getElementById('copilotObjectif');
        const rawTitre = obj ? obj.options[obj.selectedIndex].text : 'Seance Copilote';
        const titre = normalizeSavedTitle(rawTitre);
        saveGeneratedWorkout(currentExercises, titre);
    };

    window._copilotGenerate = function () {
        const objectifEl = document.getElementById('copilotObjectif');
        const niveauEl = document.getElementById('copilotNiveau');
        const dureeEl = document.getElementById('copilotDuree');
        if (!objectifEl || !niveauEl || !dureeEl) return;

        const objectif = objectifEl.value;
        const niveau = niveauEl.value;
        const duree = parseInt(dureeEl.value, 10);

        const equipChecks = document.querySelectorAll('.copilot-equip-check:checked');
        const equip = Array.from(equipChecks).map(function (c) { return c.value; });

        currentExercises = generateWorkout(objectif, niveau, duree, equip);
        renderExerciseCards(currentExercises, 'copilotResults');

        const results = document.getElementById('copilotResults');
        if (results) results.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    window._copilotParse = function () {
        const input = document.getElementById('copilotNlpInput');
        if (!input) return;
        const text = input.value.trim();
        if (!text) return;

        const result = parseNaturalLanguage(text);
        currentExercises = result.exercises;

        const p = result.parsed;
        const feedbackHtml = '<div class="copilot-parsed-feedback">' +
            '<strong>🧠 Compris :</strong> ' +
            (p.groups.join(', ')) + ' • ' +
            p.duree + ' min • ' +
            p.niveau + ' • ' +
            p.objectif +
            (p.equip.length ? ' • Equip: ' + p.equip.join(', ') : '') +
        '</div>';

        const results = document.getElementById('copilotResults');
        if (results) {
            results.innerHTML = feedbackHtml;
            renderExerciseCards(currentExercises, 'copilotResults');
        }
    };

    window._copilotTab = function (tab) {
        document.querySelectorAll('.copilot-tab').forEach(function (t) { t.classList.remove('active'); });
        document.querySelectorAll('.copilot-panel').forEach(function (p) { p.classList.remove('active'); });
        const tabEl = document.querySelector('[data-tab="' + tab + '"]');
        const panelEl = document.getElementById('copilotPanel_' + tab);
        if (tabEl) tabEl.classList.add('active');
        if (panelEl) panelEl.classList.add('active');
        const results = document.getElementById('copilotResults');
        if (results) results.innerHTML = '';
    };

    window._copilotMic = function () {
        const btn = document.getElementById('copilotMicBtn');
        const input = document.getElementById('copilotNlpInput');
        if (!speech.supported) {
            alert('La reconnaissance vocale n\'est pas supportee par votre navigateur. Utilisez Chrome ou Edge.');
            return;
        }
        speech.toggle(
            function (transcript, isFinal) {
                if (input) input.value = transcript;
                if (isFinal && btn) btn.classList.remove('mic-active');
            },
            function () { if (btn) btn.classList.remove('mic-active'); },
            function (err) { if (btn) btn.classList.remove('mic-active'); console.warn('Speech error:', err); }
        );
        if (btn) btn.classList.toggle('mic-active', speech.listening);
    };
})();

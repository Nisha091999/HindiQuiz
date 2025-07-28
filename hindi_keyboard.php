<?php
// hindi_keyboard.php - Reusable Hindi Keyboard Module
// Include this file in any PHP page to render the Hindi keyboard

function renderHindiKeyboard($inputSelector = '.hindiInput') { ?>
    <style>
        .keyboard-wrapper {
            max-width: 880px;
            margin: 15px auto;
            user-select: none;
        }
        .keyboard-instruction {
            white-space: nowrap;
            margin: 6px 0;
            padding: 6px 10px;
            background-color: #eaf2ff;
            border-left: 4px solid #3498db;
            border-radius: 6px;
            font-size: 18px;
            color: #2c3e50;
            font-weight: bold;
        }
        kbd { background: #ccc; border-radius: 4px; padding: 2px 6px; font-weight: bold; }
        .keyboard .row { display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; margin-bottom: 6px; }
        .keyboard .key {
            padding: 10px 16px;
            min-width: 40px;
            text-align: center;
            border: 1px solid #999;
            border-radius: 6px;
            background-color: #f0f0f0;
            font-size: 20px;
            cursor: pointer;
        }
        .keyboard .key:hover { background-color: #ddd; }
        .keyboard .key.special { background-color: #ccc; font-weight: bold; }
    </style>

    <div class="keyboard-instruction">
        Tip: Press <kbd>Shift</kbd> for more letters —
        <span class="hindi-text">टिप: अधिक अक्षरों के लिए <kbd>Shift</kbd> दबाएँ।</span>
    </div>
    <div class="keyboard-wrapper">
        <div class="keyboard" id="hindiKeyboard"></div>
    </div>

    <script>
        let shift = false;
        const targetSelector = "<?= $inputSelector ?>";
        const layout = [
            [{ normal: 'ऍ', shift: 'ऑ' }, { normal: '1', shift: 'ऒ' }, { normal: '2', shift: 'ऍ' }, { normal: '3', shift: 'आ' }, { normal: '4', shift: 'ई' },
             { normal: '5', shift: 'ऊ' }, { normal: '6', shift: 'भ' }, { normal: '7', shift: 'ङ' }, { normal: '8', shift: 'घ' }, { normal: '9', shift: 'ध' },
             { normal: '0', shift: 'झ' }, { normal: '-', shift: 'ञ' }, { normal: 'ृ', shift: 'ऋ' }],
            [{ normal: 'ौ', shift: 'औ' }, { normal: 'ै', shift: 'ऐ' }, { normal: 'ा', shift: 'आ' }, { normal: 'ी', shift: 'ई' }, { normal: 'ू', shift: 'ऊ' },
             { normal: 'ब', shift: 'भ' }, { normal: 'ह', shift: 'ङ' }, { normal: 'ग', shift: 'घ' }, { normal: 'द', shift: 'ध' }, { normal: 'ज', shift: 'झ' }, { normal: 'ड', shift: 'ञ' }],
            [{ normal: 'ो', shift: 'ओ' }, { normal: 'े', shift: 'ए' }, { normal: '्', shift: 'अ' }, { normal: 'ि', shift: 'इ' }, { normal: 'ु', shift: 'उ' },
             { normal: 'प', shift: 'फ' }, { normal: 'र', shift: 'ऱ' }, { normal: 'क', shift: 'ख' }, { normal: 'त', shift: 'थ' }, { normal: 'च', shift: 'छ' }, { normal: 'ट', shift: 'ठ' }],
            [{ normal: 'Shift', special: true }, { normal: 'ं', shift: 'ँ' }, { normal: 'म', shift: 'ण' }, { normal: 'न', shift: 'ऩ' }, { normal: 'व', shift: 'ऴ' },
             { normal: 'ल', shift: 'ळ' }, { normal: 'स', shift: 'श' }, { normal: 'य', shift: 'य़' }, { normal: '⌫', special: true }, { normal: '⨉', special: true }],
            [{ normal: 'Space', special: true }]
        ];

        function renderKeyboard() {
            const kb = document.getElementById('hindiKeyboard');
            kb.innerHTML = '';
            layout.forEach(row => {
                const rowDiv = document.createElement('div');
                rowDiv.className = 'row';
                row.forEach(key => {
                    const k = document.createElement('div');
                    k.className = 'key' + (key.special ? ' special' : '');
                    k.textContent = key.special ? key.normal : (shift ? key.shift : key.normal);
                    k.onclick = () => handleKey(key);
                    rowDiv.appendChild(k);
                });
                kb.appendChild(rowDiv);
            });
        }

        function handleKey(key) {
            const inputEl = document.querySelector(targetSelector);
            if (!inputEl) return;
            if (key.special) {
                if (key.normal === 'Shift') {
                    shift = !shift;
                    renderKeyboard();
                } else if (key.normal === '⌫') inputEl.value = inputEl.value.slice(0,-1);
                else if (key.normal === '⨉') inputEl.value = '';
                else if (key.normal === 'Space') inputEl.value += ' ';
            } else {
                inputEl.value += (shift ? key.shift : key.normal);
            }
            inputEl.focus();
        }

        renderKeyboard();
    </script>
<?php }

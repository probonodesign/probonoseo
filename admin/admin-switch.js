(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        var toggles = document.querySelectorAll('.probonoseo-toggle');
        
        toggles.forEach(function(toggle) {
            if (toggle.classList.contains('is-locked')) {
                toggle.addEventListener('mouseenter', function() {
                    var tooltip = this.querySelector('.probonoseo-toggle-tooltip');
                    var msg = this.dataset.offMessage || 'Pro版で利用可能';
                    if (tooltip) {
                        tooltip.textContent = msg;
                        tooltip.classList.add('show');
                    }
                });
                toggle.addEventListener('mouseleave', function() {
                    var tooltip = this.querySelector('.probonoseo-toggle-tooltip');
                    if (tooltip) {
                        tooltip.classList.remove('show');
                    }
                });
                return;
            }
            
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                
                var input = this.querySelector('input[type="hidden"]');
                if (!input) return;
                
                var isOn = this.classList.contains('is-on');
                
                if (isOn) {
                    this.classList.remove('is-on');
                    input.value = '0';
                } else {
                    this.classList.add('is-on');
                    input.value = '1';
                }
            });
        });
    });
})();
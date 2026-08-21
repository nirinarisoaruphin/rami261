// assets/js/timer.js

export default class Timer {
    constructor(timeout) {
        this.timeout = timeout;
        this.remaining = timeout;
        this.interval = null;
        this.tickCallbacks = [];
        this.timeoutCallbacks = [];
        this.isRunning = false;
        this.startTime = null;
        this.elapsed = 0;
    }
    
    // ============================================
    // CONTRÔLE
    // ============================================
    
    start() {
        if (this.isRunning) this.stop();
        
        this.isRunning = true;
        this.remaining = this.timeout;
        this.startTime = Date.now();
        this.elapsed = 0;
        
        // Premier tick immédiat
        this.tickCallbacks.forEach(cb => cb(this.remaining));
        
        this.interval = setInterval(() => {
            this.elapsed = (Date.now() - this.startTime) / 1000;
            this.remaining = Math.max(0, this.timeout - this.elapsed);
            
            this.tickCallbacks.forEach(cb => cb(Math.ceil(this.remaining)));
            
            if (this.remaining <= 0) {
                this.stop();
                this.timeoutCallbacks.forEach(cb => cb());
            }
        }, 1000);
    }
    
    stop() {
        this.isRunning = false;
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
        this.startTime = null;
    }
    
    reset() {
        this.stop();
        this.remaining = this.timeout;
        this.elapsed = 0;
        this.tickCallbacks.forEach(cb => cb(this.remaining));
    }
    
    pause() {
        if (this.isRunning) {
            this.stop();
        }
    }
    
    resume() {
        if (!this.isRunning && this.remaining > 0) {
            this.start();
        }
    }
    
    // ============================================
    // ÉVÉNEMENTS
    // ============================================
    
    onTick(callback) {
        this.tickCallbacks.push(callback);
    }
    
    onTimeout(callback) {
        this.timeoutCallbacks.push(callback);
    }
    
    // ============================================
    // GETTERS
    // ============================================
    
    getRemaining() {
        return Math.ceil(this.remaining);
    }
    
    getElapsed() {
        return Math.ceil(this.elapsed);
    }
    
    getProgress() {
        return this.elapsed / this.timeout;
    }
    
    isActive() {
        return this.isRunning;
    }
    
    getTimeLeftFormatted() {
        const remaining = Math.ceil(this.remaining);
        return `${remaining}s`;
    }
    
    // ============================================
    // BARRE DE PROGRESSION
    // ============================================
    
    createProgressBar(container) {
        const progress = document.createElement('div');
        progress.className = 'w-full h-1 bg-[var(--bg-card)] rounded-full overflow-hidden mt-1';
        progress.innerHTML = `
            <div id="timerProgress" class="h-full bg-gradient-to-r from-purple-500 to-cyan-500 transition-all duration-1000" style="width: 100%"></div>
        `;
        container.appendChild(progress);
        
        this.onTick((remaining) => {
            const bar = document.getElementById('timerProgress');
            if (bar) {
                const percent = (remaining / this.timeout) * 100;
                bar.style.width = Math.max(0, percent) + '%';
                
                // Changer la couleur quand le temps est presque écoulé
                if (remaining <= 5) {
                    bar.className = 'h-full bg-gradient-to-r from-red-500 to-orange-500 transition-all duration-1000';
                } else if (remaining <= 10) {
                    bar.className = 'h-full bg-gradient-to-r from-yellow-500 to-orange-500 transition-all duration-1000';
                } else {
                    bar.className = 'h-full bg-gradient-to-r from-purple-500 to-cyan-500 transition-all duration-1000';
                }
            }
        });
    }
}
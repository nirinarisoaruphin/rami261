// assets/js/utils.js

// ============================================
// FORMATAGE
// ============================================

export function formatDate(date) {
    const d = new Date(date);
    return d.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

export function formatMoney(amount) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2
    }).format(amount);
}

export function formatTime(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs.toString().padStart(2, '0')}`;
}

// ============================================
// CARTES
// ============================================

export function getSuitSymbol(suit) {
    const symbols = {
        hearts: '♥',
        diamonds: '♦',
        clubs: '♣',
        spades: '♠'
    };
    return symbols[suit] || '⭐';
}

export function getSuitColor(suit) {
    const colors = {
        hearts: 'red',
        diamonds: 'red',
        clubs: 'black',
        spades: 'black'
    };
    return colors[suit] || 'purple';
}

export function getCardDisplay(card) {
    if (card.is_joker) return '⭐ JOKER';
    const symbol = getSuitSymbol(card.suit);
    return `${card.value}${symbol}`;
}

export function calculateHandScore(hand) {
    return hand.reduce((sum, card) => {
        if (!card.is_joker) return sum + (card.points || 0);
        return sum;
    }, 0);
}

export function getCardValueName(value) {
    const names = {
        'A': 'As',
        '2': 'Deux',
        '3': 'Trois',
        '4': 'Quatre',
        '5': 'Cinq',
        '6': 'Six',
        '7': 'Sept',
        '8': 'Huit',
        '9': 'Neuf',
        '10': 'Dix',
        'J': 'Valet',
        'Q': 'Dame',
        'K': 'Roi'
    };
    return names[value] || value;
}

// ============================================
// UTILITAIRES
// ============================================

export function shuffleArray(array) {
    const arr = [...array];
    for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
    }
    return arr;
}

export function generateRoomCode() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let code = '';
    for (let i = 0; i < 6; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return code;
}

export function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

export function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

export function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        return navigator.clipboard.writeText(text);
    }
    // Fallback
    const textarea = document.createElement('textarea');
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    return Promise.resolve();
}

// ============================================
// DÉTECTION
// ============================================

export function detectDevice() {
    const userAgent = navigator.userAgent || navigator.vendor || window.opera;
    if (/android/i.test(userAgent)) return 'android';
    if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) return 'ios';
    if (/windows|win32|win64|wow64/i.test(userAgent)) return 'windows';
    if (/macintosh|mac os x/i.test(userAgent)) return 'mac';
    return 'desktop';
}

export function isMobile() {
    return window.innerWidth <= 768;
}

export function isTouchDevice() {
    return ('ontouchstart' in window) || 
           (navigator.maxTouchPoints > 0) || 
           (navigator.msMaxTouchPoints > 0);
}

// ============================================
// STATUTS
// ============================================

export function getStatusLabel(status) {
    const labels = {
        waiting: '⏳ En attente',
        playing: '🔄 En cours',
        finished: '🏆 Terminé',
        closed: '🔒 Fermé'
    };
    return labels[status] || status;
}

export function getWinTypeLabel(type) {
    const labels = {
        normal: 'Rami normal',
        tri_joker: '⭐ Triple Joker',
        quadri_joker: '⭐⭐ Quadruple Joker'
    };
    return labels[type] || type;
}

export function getWinTypeIcon(type) {
    const icons = {
        normal: '🎯',
        tri_joker: '⭐',
        quadri_joker: '🌟🌟'
    };
    return icons[type] || '🏆';
}

// ============================================
// VALIDATION
// ============================================

export function isValidUsername(username) {
    return /^[a-zA-Z0-9_]{3,30}$/.test(username);
}

export function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

export function isValidPassword(password) {
    return password.length >= 6;
}

// ============================================
// SESSION
// ============================================

export function getSession(key) {
    try {
        const data = sessionStorage.getItem(key);
        return data ? JSON.parse(data) : null;
    } catch {
        return null;
    }
}

export function setSession(key, value) {
    try {
        sessionStorage.setItem(key, JSON.stringify(value));
    } catch {}
}

export function removeSession(key) {
    sessionStorage.removeItem(key);
}
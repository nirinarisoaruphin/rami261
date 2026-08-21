// assets/js/websocket.js
export default class WebSocketClient {
    constructor(url) {
        this.url = url;
        this.ws = null;
        this.clientId = null;
        this.roomCode = null;
        this.userId = null;
        this.isConnected = false;
        this.listeners = new Map();
        
        this.connect();
    }
    
    connect() {
        this.ws = new WebSocket(this.url);
        
        this.ws.onopen = () => {
            this.isConnected = true;
            console.log('🟢 WebSocket connecté');
            this.emit('connected', { clientId: this.clientId });
        };
        
        this.ws.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                this.handleMessage(data);
            } catch (error) {
                console.error('Erreur message:', error);
            }
        };
        
        this.ws.onclose = () => {
            this.isConnected = false;
            console.log('🔴 WebSocket déconnecté');
            this.emit('disconnected', {});
            
            // Tentative de reconnexion après 5 secondes
            setTimeout(() => this.connect(), 5000);
        };
        
        this.ws.onerror = (error) => {
            console.error('Erreur WebSocket:', error);
            this.emit('error', error);
        };
    }
    
    handleMessage(data) {
        switch (data.type) {
            case 'connected':
                this.clientId = data.clientId;
                break;
                
            case 'player_joined':
            case 'player_left':
            case 'game_update':
                this.emit(data.type, data);
                break;
                
            default:
                console.log('Message reçu:', data);
        }
    }
    
    joinRoom(roomCode, userId) {
        if (!this.isConnected) return;
        this.roomCode = roomCode;
        this.userId = userId;
        this.send({
            type: 'join_room',
            roomCode: roomCode,
            userId: userId
        });
    }
    
    sendAction(action, data) {
        if (!this.isConnected) return;
        this.send({
            type: 'game_action',
            action: action,
            data: data
        });
    }
    
    leaveRoom() {
        if (!this.isConnected) return;
        this.send({ type: 'leave_room' });
        this.roomCode = null;
        this.userId = null;
    }
    
    send(data) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify(data));
        }
    }
    
    on(event, callback) {
        if (!this.listeners.has(event)) {
            this.listeners.set(event, []);
        }
        this.listeners.get(event).push(callback);
    }
    
    off(event, callback) {
        if (!this.listeners.has(event)) return;
        const callbacks = this.listeners.get(event);
        const index = callbacks.indexOf(callback);
        if (index > -1) {
            callbacks.splice(index, 1);
        }
    }
    
    emit(event, data) {
        if (!this.listeners.has(event)) return;
        this.listeners.get(event).forEach(cb => cb(data));
    }
    
    disconnect() {
        if (this.ws) {
            this.ws.close();
        }
    }
}
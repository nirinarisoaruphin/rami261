// websocket/server.js
const WebSocket = require('ws');
const { v4: uuidv4 } = require('uuid');

const wss = new WebSocket.Server({ port: 8080 });

// Stockage des connexions
const clients = new Map();
const rooms = new Map();

wss.on('connection', (ws) => {
    const clientId = uuidv4();
    clients.set(clientId, { ws, room: null, userId: null });
    
    console.log(`🟢 Client connecté: ${clientId}`);
    
    ws.on('message', (message) => {
        try {
            const data = JSON.parse(message);
            handleMessage(clientId, data);
        } catch (error) {
            console.error('Erreur message:', error);
        }
    });
    
    ws.on('close', () => {
        handleDisconnect(clientId);
        clients.delete(clientId);
        console.log(`🔴 Client déconnecté: ${clientId}`);
    });
    
    // Envoyer l'ID client
    ws.send(JSON.stringify({
        type: 'connected',
        clientId: clientId
    }));
});

function handleMessage(clientId, data) {
    const client = clients.get(clientId);
    if (!client) return;
    
    switch (data.type) {
        case 'join_room':
            handleJoinRoom(clientId, data.roomCode, data.userId);
            break;
            
        case 'game_action':
            handleGameAction(clientId, data);
            break;
            
        case 'leave_room':
            handleLeaveRoom(clientId);
            break;
            
        default:
            console.log(`Type inconnu: ${data.type}`);
    }
}

function handleJoinRoom(clientId, roomCode, userId) {
    const client = clients.get(clientId);
    if (!client) return;
    
    if (!rooms.has(roomCode)) {
        rooms.set(roomCode, new Set());
    }
    
    const room = rooms.get(roomCode);
    room.add(clientId);
    client.room = roomCode;
    client.userId = userId;
    
    // Notifier tous les clients de la salle
    broadcastToRoom(roomCode, {
        type: 'player_joined',
        userId: userId,
        clientId: clientId,
        players: Array.from(room).map(id => ({
            clientId: id,
            userId: clients.get(id)?.userId
        }))
    });
}

function handleGameAction(clientId, data) {
    const client = clients.get(clientId);
    if (!client || !client.room) return;
    
    broadcastToRoom(client.room, {
        type: 'game_update',
        action: data.action,
        data: data.data,
        userId: client.userId
    });
}

function handleLeaveRoom(clientId) {
    const client = clients.get(clientId);
    if (!client || !client.room) return;
    
    const room = rooms.get(client.room);
    if (room) {
        room.delete(clientId);
        
        broadcastToRoom(client.room, {
            type: 'player_left',
            userId: client.userId,
            clientId: clientId,
            players: Array.from(room).map(id => ({
                clientId: id,
                userId: clients.get(id)?.userId
            }))
        });
        
        if (room.size === 0) {
            rooms.delete(client.room);
        }
    }
    
    client.room = null;
    client.userId = null;
}

function handleDisconnect(clientId) {
    const client = clients.get(clientId);
    if (client && client.room) {
        handleLeaveRoom(clientId);
    }
}

function broadcastToRoom(roomCode, data) {
    const room = rooms.get(roomCode);
    if (!room) return;
    
    const message = JSON.stringify(data);
    room.forEach(clientId => {
        const client = clients.get(clientId);
        if (client && client.ws.readyState === WebSocket.OPEN) {
            client.ws.send(message);
        }
    });
}

console.log('🟣 Serveur WebSocket démarré sur le port 8080');
console.log('📡 En attente de connexions...');
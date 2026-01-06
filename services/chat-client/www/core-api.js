// core-api.js - Funciones críticas para comunicación con Inference Stack (AI Server)

let conversationId = null;
let leadId = null;

/**
 * Inicializa la sesión del usuario
 * Genera IDs únicos y los almacena en localStorage para mantener continuidad
 */
function initializeSession() {
    conversationId = localStorage.getItem('chat_conversation_id');
    leadId = localStorage.getItem('chat_lead_id');

    if (!leadId) {
        leadId = 'lead_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('chat_lead_id', leadId);
    }

    console.log('Session initialized:', { conversationId, leadId, clientId: CONFIG.CLIENT_ID });
}

/**
 * Envía un mensaje al servidor de IA
 * @param {string} message - Texto del mensaje a enviar
 * @returns {Promise<{response: string, sources?: any[]}>} Respuesta del bot
 */
async function sendMessageToAI(message, file = null) {
    if (!message || !message.trim()) {
        throw new Error('Message cannot be empty');
    }

    /**
     * Recolecta metadatos del lead desde URL, Browser y Configuración
     */
    function getLeadMetadata() {
        const urlParams = new URLSearchParams(window.location.search);

        // Helper para obtener de URL o null
        const getParam = (key) => urlParams.get(key) || null;

        return {
            // ID fields
            source_id: getParam('source_id'),
            origin_channel_id: getParam('origin_channel_id'),

            // UTMs
            utm_source: getParam('utm_source'),
            utm_medium: getParam('utm_medium'),
            utm_campaign: getParam('utm_campaign'),
            utm_content: getParam('utm_content'),
            utm_term: getParam('utm_term'),

            // Tracking
            click_id: getParam('click_id'),
            click_id_type: getParam('click_id_type'),
            referrer_url: document.referrer || null,
            landing_page_url: window.location.href,
            ip_address: null, // El servidor lo resolverá
            user_agent: navigator.userAgent,

            // Property Info
            source_property_ref: getParam('source_property_ref') || getParam('ref'),
            source_property_url: getParam('source_property_url'),
            estimated_value: getParam('estimated_value'),
            property_snapshot: getParam('property_snapshot')
        };
    }

    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 60000); // 60 segundos timeout por RAG

        const metadata = getLeadMetadata();

        const payload = {
            client_id: CONFIG.CLIENT_ID,
            lead_id: leadId,
            conversation_id: conversationId,
            queryText: message.trim(),
            ...metadata // Expandir metadatos en el root, o dentro de 'metadata' según requiera el backend. 
            // Asumiendo root por la lista plana dada por el usuario.
        };

        // Debug: Mostrar payload en consola
        console.log('📤 Sending payload to AI:', payload);

        // El servidor de IA espera estos campos según el manual
        const response = await fetch(`${CONFIG.API_URL}/chat`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload),
            signal: controller.signal
        });

        clearTimeout(timeoutId);

        if (!response.ok) {
            throw new Error(`Error del servidor IA: ${response.status}`);
        }

        const data = await response.json();

        if (data && data.answer) {
            // Guardar el conversation_id si es nuevo
            if (data.conversation_id && data.conversation_id !== conversationId) {
                conversationId = data.conversation_id;
                localStorage.setItem('chat_conversation_id', conversationId);
            }
            return {
                response: data.answer,
                sources: data.sources || []
            };
        } else {
            throw new Error('No se recibió respuesta del bot');
        }
    } catch (err) {
        if (err.name === 'AbortError') {
            throw new Error('La IA está tardando demasiado en responder. Intenta de nuevo.');
        }
        console.error('Error in sendMessageToAI:', err);
        throw err;
    }
}

/**
 * Recupera el historial de chat del servidor
 */
async function fetchChatHistory() {
    if (!conversationId) return [];

    try {
        const response = await fetch(`${CONFIG.API_URL}/chat/${conversationId}`);
        if (!response.ok) return [];
        return await response.json();
    } catch (err) {
        console.error('Error fetching history:', err);
        return [];
    }
}

/**
 * Resetea la sesión (logout)
 */
function resetSession() {
    localStorage.removeItem('chat_conversation_id');
    localStorage.removeItem('chat_lead_id');
    conversationId = null;
    leadId = null;
}

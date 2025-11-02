import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Echo + Pusher (Soketi)
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const wsHost = import.meta.env.VITE_PUSHER_HOST || window.location.hostname;
const wsPort = Number(import.meta.env.VITE_PUSHER_PORT || 6001);
const scheme = import.meta.env.VITE_PUSHER_SCHEME || 'http';

window.Echo = new Echo({
	broadcaster: 'pusher',
	key: import.meta.env.VITE_PUSHER_APP_KEY,
	cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
	wsHost,
	wsPort,
	wssPort: wsPort,
	forceTLS: scheme === 'https',
	enabledTransports: ['ws', 'wss'],
	withCredentials: true,
});

// Adjuntar X-Socket-Id a las peticiones para evitar eco duplicado
try {
	const setSocketHeader = () => {
		const id = window.Echo.socketId && window.Echo.socketId();
		if (id) {
			window.axios.defaults.headers.common['X-Socket-Id'] = id;
		}
	};
	setSocketHeader();
	if (window.Echo.connector?.pusher?.connection) {
		window.Echo.connector.pusher.connection.bind('connected', setSocketHeader);
	}
} catch (e) {
	// noop
}

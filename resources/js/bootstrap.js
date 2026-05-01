// Name : Rodain Gouzlan Id:
// إعدادات JavaScript العامة للواجهة (يتم استدعاؤها من app.js).
// تحميل lodash كأداة مساعدة عامة.
window._ = require('lodash');

// إعداد axios لإرسال الطلبات إلى Laravel مع تفعيل XSRF تلقائياً.
/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// إعداد Laravel Echo (تعليقات محفوظة كقالب جاهز للبث الفوري إن احتاج المشروع).
/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });
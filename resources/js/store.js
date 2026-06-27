// Name : Rodain Gouzlan Id:
// ملف JavaScript الخاص بصفحة "Branch Directory".
// الهدف: تنفيذ البحث والفلترة عبر AJAX بدون إعادة تحميل الصفحة.
// ملاحظة: يعتمد على jQuery المتوفر في layout.

(function () {
    // تأكد من توفر jQuery قبل تشغيل أي كود.
    if (typeof window === 'undefined' || typeof window.$ === 'undefined') {
        return;
    }

    // ننتظر جاهزية DOM لضمان وجود العناصر المطلوبة.
    $(function () {
        // إذا كانت الصفحة ربطت السلوك مسبقاً (مثل السكربت المضمّن) نتوقف هنا.
        if (window.__storeSearchWired) {
            return;
        }
        window.__storeSearchWired = true;

        const $form = $('#search-form');
        const $grid = $('#stores-grid');

        // إذا لم تكن الصفحة الحالية هي صفحة الفروع فلا ننفّذ أي شيء.
        if (!$form.length || !$grid.length) {
            return;
        }

        // مؤقت للـ debounce حتى لا نرسل طلب عند كل حرف مباشرة.
        let timerId = null;
        // مرجع للطلب الحالي حتى نلغيه عند بدء طلب جديد.
        let currentRequest = null;

        function refreshStores() {
            // إلغاء أي طلب سابق لم يكتمل بعد.
            if (currentRequest && typeof currentRequest.abort === 'function') {
                currentRequest.abort();
            }

            // إرسال الطلب بنفس رابط الـ form وبنفس الفلاتر الحالية.
            const url = $form.attr('action');
            currentRequest = $.get(url, $form.serialize())
                .done(function (html) {
                    // استبدال محتوى شبكة الفروع بالنتيجة الجديدة.
                    $grid.html(html);
                })
                .always(function () {
                    // تفريغ المرجع بعد انتهاء الطلب.
                    currentRequest = null;
                });
        }

        // تشغيل البحث عند تغيّر أي input/select مع تأخير بسيط.
        $form.on('input change', 'input, select', function () {
            clearTimeout(timerId);
            timerId = setTimeout(refreshStores, 400);
        });

        // زر Reset يرجع الفلاتر للوضع الافتراضي ثم يعيد التحميل.
        $('#reset-filters').on('click', function () {
            $form.find('input[type="text"]').val('');
            $form.find('select').prop('selectedIndex', 0);
            refreshStores();
        });
    });
})();

// Summary: يفعّل البحث والفلاتر في صفحة شبكة الفروع عبر AJAX مع debounce وإلغاء الطلبات المتداخلة.
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('checkoutForm');
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    const paypalContainer = document.getElementById('paypalButtonContainer');
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');

    if (!form || !placeOrderBtn) return;

    function getSelectedPayment() {
        const selected = document.querySelector('input[name="payment_method"]:checked');
        return selected ? selected.value : 'cod';
    }

    function validateForm() {
        const required = ['full_name', 'email', 'phone', 'address'];
        for (const field of required) {
            const input = form.querySelector(`[name="${field}"]`);
            if (!input || !input.value.trim()) {
                input?.focus();
                return false;
            }
        }
        const email = form.querySelector('[name="email"]');
        if (email && !email.value.includes('@')) {
            email.focus();
            return false;
        }
        return true;
    }

    function getFormData() {
        return {
            full_name: form.querySelector('[name="full_name"]').value.trim(),
            email: form.querySelector('[name="email"]').value.trim(),
            phone: form.querySelector('[name="phone"]').value.trim(),
            address: form.querySelector('[name="address"]').value.trim(),
            city: form.querySelector('[name="city"]').value,
            notes: form.querySelector('[name="notes"]').value.trim(),
        };
    }

    function togglePaymentUI() {
        const method = getSelectedPayment();
        const paypalOn = method === 'paypal' && window.BAMBE_CHECKOUT?.paypalEnabled;

        placeOrderBtn.hidden = paypalOn;
        if (paypalContainer) {
            paypalContainer.hidden = !paypalOn;
        }
    }

    paymentRadios.forEach((radio) => {
        radio.addEventListener('change', togglePaymentUI);
    });

    togglePaymentUI();

    if (window.BAMBE_CHECKOUT?.paypalEnabled && typeof paypal !== 'undefined' && paypalContainer) {
        paypal.Buttons({
            style: { layout: 'vertical', color: 'blue', shape: 'rect', label: 'paypal' },
            createOrder: async () => {
                if (!validateForm()) {
                    throw new Error('Please fill in all required delivery details.');
                }

                const response = await fetch('api/paypal-create-order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                });
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Failed to create PayPal order');
                }

                return data.paypal_order_id;
            },
            onApprove: async (data) => {
                placeOrderBtn.disabled = true;
                placeOrderBtn.textContent = 'Processing payment...';

                const payload = {
                    paypal_order_id: data.orderID,
                    ...getFormData(),
                };

                const response = await fetch('api/paypal-capture.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });
                const result = await response.json();

                if (!response.ok) {
                    alert(result.error || 'Payment failed. Please try again.');
                    placeOrderBtn.disabled = false;
                    placeOrderBtn.textContent = 'Place Order';
                    return;
                }

                window.location.href = result.redirect;
            },
            onError: (err) => {
                console.error('PayPal error:', err);
                alert('PayPal payment could not be completed. Please try again or use Cash on Delivery.');
            },
        }).render('#paypalButtonContainer');
    }
});

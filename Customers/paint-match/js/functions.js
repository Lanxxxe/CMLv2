var paymentMethod = document.querySelector('#payment-method');
var proofOfPayment = document.querySelector('#transaction-image');
var getItemPrice = document.querySelector('#price').value;
var getUserQuantityInput = document.querySelector('#quantity');
var totalBillInput = document.querySelector('#totalBill');


paymentMethod.addEventListener('change', () => {
    var payment = paymentMethod.value;
    payment === 'On the Counter' ? proofOfPayment.disabled = true : proofOfPayment.disabled = false; 

})

getUserQuantityInput.addEventListener('input', () => {
    let totalPrice = getItemPrice * getUserQuantityInput.value;
    Number(totalPrice) > 0 ? totalBillInput.value = totalPrice : totalBillInput.value = '';


})


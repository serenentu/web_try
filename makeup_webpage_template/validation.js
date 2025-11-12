// validation.js - basic client-side validation
document.addEventListener('DOMContentLoaded', function(){
  var form = document.getElementById('checkoutForm');
  if (!form) return;
  form.addEventListener('submit', function(e){
    var email = form.querySelector('[name=email]').value.trim();
    var name = form.querySelector('[name=name]').value.trim();
    var phone = form.querySelector('[name=phone]').value.trim();
    var address = form.querySelector('[name=address]').value.trim();
    var errs = [];
    if (name.length < 2) errs.push('Name must be at least 2 characters.');
    if (!/^\S+@\S+\.\S+$/.test(email)) errs.push('Invalid email.');
    if (phone.length < 6) errs.push('Phone seems too short.');
    if (address.length < 6) errs.push('Provide a fuller address.');
    if (errs.length) {
      e.preventDefault();
      alert(errs.join('\n'));
    }
  });
});

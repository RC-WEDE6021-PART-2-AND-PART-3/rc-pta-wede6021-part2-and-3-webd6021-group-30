
'use strict';

function toggleFaq(btn) {
  const isOpen = btn.classList.contains('open');
  
  document.querySelectorAll('.faq-q.open').forEach(function(b) {
    b.classList.remove('open');
    const ico = b.querySelector('[data-lucide]');
    if (ico) { ico.setAttribute('data-lucide','plus'); lucide.createIcons(); }
    const ans = b.nextElementSibling;
    if (ans && ans.classList.contains('faq-a')) ans.remove();
  });
  if (!isOpen) {
    btn.classList.add('open');
    const ico = btn.querySelector('[data-lucide]');
    if (ico) { ico.setAttribute('data-lucide','minus'); lucide.createIcons(); }
    const answer = btn.getAttribute('data-answer');
    if (answer) {
      const div = document.createElement('div');
      div.className = 'faq-a';
      div.textContent = answer;
      btn.insertAdjacentElement('afterend', div);
    }
  }
}

function showPricePopup(event, name, price) {
  event.preventDefault();
  var fmt = 'R' + parseFloat(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  alert('Sell Price: ' + fmt + '\n\n' + name + '\n\nClick OK to add to your cart.');
  event.target.submit();
}

document.addEventListener('DOMContentLoaded', function() {
  if (typeof lucide !== 'undefined') lucide.createIcons();
  var toast = document.getElementById('cartToast');
  if (toast) setTimeout(function() { toast.style.display = 'none'; }, 3500);
});

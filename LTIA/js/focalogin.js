const container = document.querySelector('.container');
container.addEventListener('animationend', function() {
    container.classList.remove('shake');
});
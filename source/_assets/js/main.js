document.addEventListener('DOMContentLoaded', function () {
    var button = document.querySelector('.nav-toggle');
    var nav = document.querySelector('#main-nav');

    if (!button || !nav) {
        return;
    }

    button.addEventListener('click', function () {
        var expanded = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        nav.classList.toggle('is-open', !expanded);
    });
});

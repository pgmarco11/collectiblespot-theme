// Import styles so Webpack can bundle and output to public/css
import '../styles/_index.scss';

// Example: Console log to test if the script loads
console.log("collectible-spot theme JavaScript loaded!");

document.addEventListener('DOMContentLoaded', function () {
    const dropdownLinks = document.querySelectorAll(
        '.navbar .dropdown-toggle[href]'
    );

    dropdownLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            if (window.innerWidth >= 992) {
                return;
            }

            const parent = link.closest('.dropdown');
            const menu = parent
                ? parent.querySelector('.dropdown-menu')
                : null;

            const isOpen =
                link.classList.contains('show') ||
                (menu && menu.classList.contains('show'));

            if (isOpen) {
                event.preventDefault();
                event.stopImmediatePropagation();

                window.location.href = link.href;
            }
        });
    });
});
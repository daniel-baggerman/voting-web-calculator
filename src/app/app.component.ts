import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent implements OnInit{
  title = 'voting-web-calculator';

  ngOnInit(){
    // Mobile menu toggle button
    const mobileMenuButton = document.querySelector('.btn--nav-menu'),
    mobileMenuLinks = document.querySelector('.nav__links'),
    mobileMenuShadow = document.querySelector('.nav__links-shadow'),
    navMenuLinks = document.querySelectorAll('.nav__links a, .nav__links button');

    mobileMenuButton.addEventListener('click', function () {
        mobileMenuLinks.classList.toggle('nav__links--expanded');
    });

    mobileMenuShadow.addEventListener('click', function () {
        mobileMenuLinks.classList.remove('nav__links--expanded');
    });

    for (let i = 0; i < navMenuLinks.length; i++) {
        navMenuLinks[i].addEventListener('click', function() {
            mobileMenuLinks.classList.remove('nav__links--expanded');
        });
    }

// ---------------------------------------------- //
// Toggle light/dark mode                         //
// ---------------------------------------------- //

    // Remove no-js class from root <html> element so the toggle button displays when JS is enabled
    document.documentElement.classList.remove('no-js');

    // Key to store user's preference in localSotrage so it doesn't reset each time they visit
    const STORAGE_KEY = 'user-color-scheme';
    const COLOR_MODE_KEY = '--color-mode';

    const modeToggleButton = document.querySelector('.color-scheme-toggle');

    // Returns string of either "light" or "dark" depending on the media query situation
    const getCSSCustomProp = propKey => {
        let response = getComputedStyle(document.documentElement).getPropertyValue(propKey);

        if (response.length) {
            response = response.replace(/\"/g, '').trim();
        }

        return response;
    };

    // Load and apply the setting that's either manually set or in localStorage
    const applySetting = (passedSetting:string = '') => {
        let currentSetting = passedSetting || localStorage.getItem(STORAGE_KEY);

        if (currentSetting) {
            document.documentElement.setAttribute('data-user-color-scheme', currentSetting);
        }
    };

    // Toggle the current color mode setting and save it to localStorage
    const toggleSetting = () => {
        let currentSetting = localStorage.getItem(STORAGE_KEY);

        switch (currentSetting) {
            case null:
                currentSetting = getCSSCustomProp(COLOR_MODE_KEY) === 'dark' ? 'light' : 'dark';
                break;
            case 'light':
                currentSetting = 'dark';
                break;
            case 'dark':
                currentSetting = 'light';
                break;
        }

        localStorage.setItem(STORAGE_KEY, currentSetting);

        return currentSetting;
    };

    // Make all the above crap happen when you click the toggle button
    modeToggleButton.addEventListener('click', evt => {
        evt.preventDefault();

        applySetting(toggleSetting());
    });

    // Apply everything by default so the user first gets the color mode their system prefers
    applySetting();
  }
}

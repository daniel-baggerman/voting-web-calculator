import { Component, OnInit } from '@angular/core';
import { MessagingService } from 'src/app/core/messaging.service';

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.css']
})
export class HeaderComponent implements OnInit {
  // Key to store user's preference in localSotrage so it doesn't reset each time they visit
  STORAGE_KEY = 'user-color-scheme';
  COLOR_MODE_KEY = '--color-mode';

  modeToggleButton = document.querySelector('.color-scheme-toggle');

  constructor(private messaging_service: MessagingService) { }

  ngOnInit() {
    // Remove no-js class from root <html> element so the toggle button displays when JS is enabled
    document.documentElement.classList.remove('no-js');
    // Apply color scheme by default so the user first gets the color mode their system prefers
    this.applySetting();
  }

  // ---------------------------------------------- //
  // Toggle light/dark mode                         //
  // ---------------------------------------------- //
  // Returns string of either "light" or "dark" depending on the media query situation
  getCSSCustomProp(propKey){
    let response = getComputedStyle(document.documentElement).getPropertyValue(propKey);

    if (response.length) {
        response = response.replace(/\"/g, '').replace(/\'/g, '').trim();
    }

    return response;
  }

  // Toggle the current color mode setting and save it to localStorage
  toggleSetting(){
    let currentSetting = localStorage.getItem(this.STORAGE_KEY);

    switch (currentSetting) {
        case null:
            currentSetting = this.getCSSCustomProp(this.COLOR_MODE_KEY) === 'dark' ? 'light' : 'dark';
            break;
        case 'light':
            currentSetting = 'dark';
            break;
        case 'dark':
            currentSetting = 'light';
            break;
    }

    localStorage.setItem(this.STORAGE_KEY, currentSetting);

    return currentSetting;
  }

  applySetting = (passedSetting:string = '') => {
    let currentSetting = passedSetting 
                          || localStorage.getItem(this.STORAGE_KEY) 
                          || this.getCSSCustomProp(this.COLOR_MODE_KEY);

    if (currentSetting) {
        document.documentElement.setAttribute('data-user-color-scheme', currentSetting);
        localStorage.setItem(this.STORAGE_KEY, currentSetting);
        this.messaging_service.light_dark_toggle.next( localStorage.getItem(this.STORAGE_KEY) );
    }
  }
}

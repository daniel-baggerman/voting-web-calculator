import { Component, OnInit } from '@angular/core';
import { Subject } from 'rxjs';
import { MessagingService } from './messaging.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent implements OnInit{
  title = 'voting-web-calculator';

  constructor(){}

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
  }
}

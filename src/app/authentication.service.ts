import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router, CanActivateChild } from '@angular/router';
import { Observable } from 'rxjs';
import { Injectable } from '@angular/core';

@Injectable()
export class AuthenticationService implements CanActivate, CanActivateChild {
    temp: boolean;

    constructor(private router: Router){ }

    canActivate(route: ActivatedRouteSnapshot,
                state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {
        return this.is_authenticated().then(
            (authenticated: boolean) => {
                if(authenticated) {
                    return true;
                } else {
                    this.router.navigate(['/election_search']);
                }
            }
        )
    }
    
    canActivateChild(   route: ActivatedRouteSnapshot,
                        state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {
        let temp2 = this.temp;
        this.temp = true;
        return temp2;
        // return this.canActivate(route, state);
    }

    is_authenticated(){
        const promise = new Promise(
            (resolve, reject) => {
                resolve(true); // TODO: send request to server to check user authentication
            }
        )
        return promise; 
    }
}
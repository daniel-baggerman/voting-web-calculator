import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router, CanActivateChild } from '@angular/router';
import { Observable } from 'rxjs';
import { Injectable } from '@angular/core';

@Injectable()
export class AuthenticationService implements CanActivate, CanActivateChild {
    constructor(private router: Router){ }

    canActivate(route: ActivatedRouteSnapshot,
                state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {
        return this.is_authenticated().then(
            (authenticated: boolean) => {
                if(authenticated) {
                    return true;
                } else {
                    this.router.navigate(['/']); // TODO: navigate to some warning page or something
                }
            }
        )
    }
    
    canActivateChild(   route: ActivatedRouteSnapshot,
                        state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {
        return this.canActivate(route, state);
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
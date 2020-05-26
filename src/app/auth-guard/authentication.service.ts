import { Injectable } from '@angular/core';
import { Subject, Observable } from 'rxjs';
import { tap, shareReplay, switchMap, catchError } from 'rxjs/operators';
import { User } from '../shared/user.model';
import { HttpClient } from '@angular/common/http';
import { JwtHelperService } from '@auth0/angular-jwt';
import { GlobalConstants } from '../shared/global-constants';
import { http_response } from '../shared/http_response.model';

@Injectable({providedIn: 'root'})
export class AuthenticationService {
    user = new Subject<User>(); // Stores the user information as they browse.
    private jwtHelper = new JwtHelperService();

    constructor(private http: HttpClient){ }

    login(url_election_name: string, code: string ){
        console.log('login');
        console.log({ url_election_name: url_election_name, code: code });

        return this.http
            .post(
                GlobalConstants.apiURL+'backend/login.php',
                { url_election_name: url_election_name,
                  code: code },
                { responseType: 'text' }
            )
            .pipe(
                tap( (token: string) => {
                    localStorage.setItem('token',token);
                }),
                shareReplay()
            );
    }

    can_vote(expected_url_election_name: string): Observable<boolean> | boolean {
        const token = localStorage.getItem('token');
        // console.log(token);
        // console.log(this.jwtHelper.isTokenExpired(token));
        // console.log(this.jwtHelper.decodeToken(token));
        
        if (token){
            const token_payload = this.jwtHelper.decodeToken(token);
            if ( !this.jwtHelper.isTokenExpired(token) && token_payload.uen === expected_url_election_name ){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
        
    }
}
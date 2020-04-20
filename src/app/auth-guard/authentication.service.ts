import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';
import { tap, pluck, shareReplay } from 'rxjs/operators';
import { User } from '../shared/user.model';
import { HttpClient } from '@angular/common/http';
import { http_response } from '../shared/http_response.model';
import { JwtHelperService } from '@auth0/angular-jwt';
import { decode } from 'jwt-decode';
import { GlobalConstants } from '../shared/global-constants';

@Injectable({providedIn: 'root'})
export class AuthenticationService {
    user = new Subject<User>(); // Stores the user information as they browse.
    private jwtHelper = new JwtHelperService();

    constructor(private http: HttpClient){ }

    login(url_election_name: string, code: string ){
        console.log('login')
        console.log({ url_election_name: url_election_name, code: code })

        return this.http
            .post<http_response>(
                GlobalConstants.apiURL+'backend/login.php',
                { url_election_name: url_election_name,
                  code: code }
            )
            .pipe(
                // pluck('data'),
                tap( (http_response: http_response) => {
                    console.log('login')
                    console.log(http_response)
                    localStorage.setItem('token',http_response.data.token);
                }),
                shareReplay()
            );
    }

    can_vote(url_election_name: string): boolean {
        const token = localStorage.getItem('token');
        
        if (token){
            const token_payload = decode(token);
            if ( !this.jwtHelper.isTokenExpired(token) && token_payload.election == url_election_name ){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
        
    }
}
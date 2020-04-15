import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';
import { tap } from 'rxjs/operators';
import { User } from './shared/user.model';
import { HttpClient } from '@angular/common/http';
import { http_response } from './shared/http_response.model';

@Injectable({providedIn: 'root'})
export class AuthenticationService {
    user = new Subject<User>(); // Stores the user information as they browse.

    constructor(private http: HttpClient){ }

    login(user_id: string, password: string, ){
        return this.http
            .post<http_response>(
                'login.url',
                { user_id: user_id,
                  password: password }
            )
            .pipe(
                tap(
                    (http_response: http_response) => {
                        const user = new User(http_response.data.voter_id, http_response.data.role, http_response.data.token);
                        this.user.next(user);
                    }
                )
            );
    }
}
import { Injectable } from "@angular/core";
import { HttpInterceptor, HttpRequest, HttpHandler } from '@angular/common/http';
import { AuthenticationService } from './authentication.service';

@Injectable()
export class AuthInterceptorService implements HttpInterceptor {
    constructor(private auth_service: AuthenticationService){}

    intercept(req: HttpRequest<any>, next: HttpHandler){
        const token = localStorage.getItem('token');

        if (token) {
            const cloned_token = req.clone({
                headers: req.headers.set("Authorization","Bearer "+token)
            });

            return next.handle(cloned_token);
        } else {
            return next.handle(req);
        }
    }
}
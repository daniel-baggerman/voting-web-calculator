import { CommonModule } from '@angular/common';
import { NgModule, Optional, SkipSelf } from '@angular/core';
import { HTTP_INTERCEPTORS } from '@angular/common/http';
import { AuthGuard } from './auth-guard/auth.guard';
import { AuthInterceptorService } from './auth-guard/auth-interceptor.service';
import { ErrorIntercept } from './error.interceptor';
import { JwtHelperService } from '@auth0/angular-jwt';

@NgModule({
    imports: [ 
        CommonModule
    ],
    declarations: [],
    exports: [],
    providers: [AuthGuard,
        {provide: HTTP_INTERCEPTORS, useClass: AuthInterceptorService, multi: true},
        {provide: HTTP_INTERCEPTORS, useClass: ErrorIntercept, multi: true},
        JwtHelperService]
})

export class CoreModule {
    constructor(@Optional() @SkipSelf() core:CoreModule ){
        if (core) {
            throw new Error("You should import core module only in the root module")
        }
      }
 }
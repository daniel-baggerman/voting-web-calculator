import { Injectable } from '@angular/core';
import { AuthenticationService } from './authentication.service';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, UrlTree, Router, ActivatedRoute, ParamMap} from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { GlobalConstants } from '../shared/global-constants';
import { of, Observable, iif } from 'rxjs';
import { switchMap, map } from 'rxjs/operators';
import { http_response } from '../shared/http_response.model';

@Injectable({
  providedIn: 'root'
})
export class AuthGuard implements CanActivate {

  constructor(private auth_service: AuthenticationService,
              private router: Router,
              private activated_route: ActivatedRoute,
              private http: HttpClient) { }

  canActivate(route: ActivatedRouteSnapshot,
              state: RouterStateSnapshot): UrlTree | Observable<boolean | UrlTree>
  {
    /*
        1. If the election doesn't require password, allow -> ballot page
        2. If it does require a password, authenticate user
            a. fails auth -> login page
            b. passes auth -> ballot page
    */

    // Var to to hold election name from url
    let expected_election = '';

    // Get the election name from URL and store it.
    if (route.root.firstChild.paramMap.has('election_name')){
      expected_election = route.root.firstChild.paramMap.get('election_name');
    } else {
      // If we can't, return to root
      return this.router.createUrlTree(['/']);
    }

    return this.http.get<http_response>(GlobalConstants.apiURL+'backend/get_election_type.php?url_election_name='+expected_election)
    .pipe(
      // Map the data in the response to an object
      map(
        (http_response: http_response) => {
          return { public_private:    +http_response.data.public_private,
                   password_protect:  +http_response.data.password_protect }
        }
      ),
      map(
        (election_type: {public_private: number, password_protect: number}) => {
          // If it's a public election with no password, let them pass.
          if(election_type.public_private === 1 && election_type.password_protect === 0) {
            return true;
          } else {
            // If it's a public election with a password, check token to see if they can vote in the expected election
            if (this.auth_service.can_vote(expected_election)){
              return true;
            } else {
            // If they aren't authenticated, navigate to the page where they can authenticate.
              return this.router.createUrlTree(['/'+expected_election,'authenticate']);
            }
          }
        }
      )
    );

    /*
    // Problem is that activated_route is not instantiated yet when authguard gets called so there is nothing in the paramMap
    return this.activated_route.paramMap.pipe(
      // Retrieve election name from route paramMap
      map(
        (param_map: ParamMap) => {
          if(param_map.has('election_name')){
            return param_map.get('election_name')
          }
        }
      ),
      // Use election name to fetch election type via http request and then merge the response into an object that holds the election_name and response data
      switchMap(
        (expected_election: string) => {
          return this.http.get<http_response>(GlobalConstants.apiURL+'backend/get_election_type.php?url_election_name='+expected_election).pipe(map((response) => ({ expected_election: expected_election, response: response})));
        }
      ),
      // Format it all into a nicer object
      map(
        (http_response: {expected_election: string, response: http_response}) => {
          return {expected_election:  http_response.expected_election,
                  public_private:    +http_response.response.data.public_private,
                  password_protect:  +http_response.response.data.password_protect }
        }
      ),
      // Use the election type from the response to determine what kind of access they should have
      map(
        (election_type: {expected_election: string, public_private: number, password_protect: number}) => {
          // If it's a public election with no password, let them pass.
          if(election_type.public_private === 1 && election_type.password_protect === 0) {
            return true;
          } else {
            // If it's a public election with a password, check token to see if they can vote in the expected election
            if (this.auth_service.can_vote(election_type.expected_election)){
              return true;
            } else {
            // If they aren't authenticated, navigate to the page where they can authenticate.
              return this.router.createUrlTree(['/'+election_type.expected_election,'authenticate']);
            }
          }
        }
      )
    )*/
  }
}

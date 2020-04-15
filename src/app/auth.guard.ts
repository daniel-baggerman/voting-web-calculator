import { Injectable } from '@angular/core';
import { AuthenticationService } from './authentication.service';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, UrlTree, Router, ActivatedRoute, RoutesRecognized, ParamMap, GuardsCheckEnd, GuardsCheckStart} from '@angular/router';
import { Subscription, Observable, of } from 'rxjs';
import { take, map, filter, first} from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class AuthGuard implements CanActivate {
  auth_return: boolean | UrlTree | Promise<boolean | UrlTree> | Observable<boolean | UrlTree>;

  constructor(private auth_service: AuthenticationService,
              private router: Router,
              private activated_route: ActivatedRoute) { }

  canActivate(
    route: ActivatedRouteSnapshot,
    state: RouterStateSnapshot
  ): boolean | UrlTree | Promise<boolean | UrlTree> | Observable<boolean | UrlTree>{

    // Fetch route election_name param

    // look at the router to see what election they are trying to access
    this.router.events.pipe(
      // Debugging
      // map((event)=>{
      //   console.log(event)
      //   return event;
      // }),
      // filter to just RoutesRecognized or GuardsCheckEnd events for different possible navigation paths to the ballot cast component
      filter((event) => (event instanceof RoutesRecognized || event instanceof GuardsCheckEnd) ? true : false),
      take(1),
      // grab the root from the event
      map((event: RoutesRecognized | GuardsCheckEnd) => {
        console.log('event map post filter')
        return event.state.root;
      }),
      // probs need to flatmap/switchmap here to run http request to authenticate user or get their data from server, then use that next to match to the url_election_name and authenticate
      // grab the election name from that root tree
      map((route: ActivatedRouteSnapshot) => {
        if(route.firstChild){
          if(route.firstChild.paramMap.has('election_name')){
            console.log('getting election name')
            return route.firstChild.paramMap.get('election_name');
          } else {
            console.log('no election name')
            return false;
          }
        }
      }),
      // use the election name to check if they should have access
      map((url_election_name: string) => {
        let is_auth = true; // some check if they election is allowed to be accessed
        if(is_auth){
          console.log('auth=true')
          return true;
        } else {
          console.log('url_tree')
          return this.router.createUrlTree(['/'+url_election_name,'authenticate']); // pass navigation array, e.g. [':election_name','auth']
        }
      })
    ).subscribe(
      (val) => {
        this.auth_return = val;
      }
    );

    return this.auth_return;
  }
}

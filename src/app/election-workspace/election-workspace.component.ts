import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, ParamMap, Router } from '@angular/router';
import { http_response } from '../shared/http_response.model';
import { ManageElectionService } from './manage-election.service';
import { DBTransactions } from '../db_transactions.service';
import { switchMap, tap, catchError } from 'rxjs/operators';
import { EMPTY, Subscription } from 'rxjs';
import { HttpErrorResponse } from '@angular/common/http';

@Component({
  selector: 'app-election-workspace',
  templateUrl: './election-workspace.component.html',
  styleUrls: ['./election-workspace.component.css']
})
export class ElectionWorkspaceComponent implements OnInit {
  valid_election_url: boolean;
  cast_vote_mode = true;

  route_sub: Subscription;

  constructor(private route: ActivatedRoute,
              private election_manager: ManageElectionService,
              private trans: DBTransactions,
              private router: Router) { }

  ngOnInit() {
    // Use the election name in the URL to fetch the election data and store it. 
    let url_election_name: string;
    this.route_sub = this.route.paramMap
    .pipe(
      // Make request to database to fetch database details based on election in URL
      switchMap((paramMap: ParamMap) => {
        if (paramMap.has('election_name')){
          url_election_name = paramMap.get('election_name');
          return this.trans.get_election_from_url_name(url_election_name);
        }
      }),
      // Catch any errors from http request
      catchError(
        (error: HttpErrorResponse) => {
          this.valid_election_url = false;
          alert( error.error.text )
          console.log(error);
          return EMPTY;
        }
      ),
      // Update the election details in election_manager based on http response
      tap(
        (http_response: http_response) => {
          console.log(http_response.data)
          if(http_response.data && http_response.data.length !==0){
            this.election_manager.election = {  election_id: http_response.data[0].election_id, 
                                                description: http_response.data[0].description, 
                                                long_description: http_response.data[0].long_description, 
                                                url_election_name: url_election_name,
                                                public_private: http_response.data[0].public_private,
                                                password_protect: http_response.data[0].password_protect };
            this.valid_election_url = true;
          } else {
            this.valid_election_url = false;
          }
        }
      ),
      // Check if we are only at the election workspace page without any component loaded. If so, nav to cast vote component.
      tap(() => {
        let url = location.pathname.split('/');
        if (this.election_manager.election.url_election_name == url[url.length-1]){
          this.router.navigate(['vote'], {relativeTo: this.route});
        }
      })
    )
    .subscribe();
  }
}

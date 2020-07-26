import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute, ParamMap, Router } from '@angular/router';
import { http_response } from 'src/app/core/models/http_response.model';
import { ManageElectionService } from './manage-election.service';
import { DBTransactions } from '../core/db_transactions.service';
import { switchMap, tap } from 'rxjs/operators';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-election-workspace',
  templateUrl: './election-workspace.component.html',
  styleUrls: ['./election-workspace.component.css']
})
export class ElectionWorkspaceComponent implements OnInit, OnDestroy {
  valid_election_url: Promise<boolean>;
  election_date_loaded: Promise<boolean>;
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
      // Update the election details in election_manager based on http response
      tap(
        (http_response: http_response) => {
          // console.log(http_response.data)
          if(http_response.data && http_response.data.length !==0){
            this.election_manager.election = {  election_id: http_response.data[0].election_id, 
                                                description: http_response.data[0].description, 
                                                long_description: http_response.data[0].long_description, 
                                                url_election_name: url_election_name,
                                                public_private: http_response.data[0].public_private,
                                                password_protect: http_response.data[0].password_protect,
                                                start_date: http_response.data[0].start_date,
                                                end_date: http_response.data[0].end_date };
            this.election_date_loaded = Promise.resolve(true);
            this.valid_election_url = Promise.resolve(true);

            // Check if we are only at the election workspace page without any component loaded. If so, nav to cast vote component.
            let url = location.pathname.split('/');
            if (this.election_manager.election.url_election_name == url[url.length-1]){
              this.router.navigate(['vote'], {relativeTo: this.route});
            }

          } else {
            this.valid_election_url = Promise.resolve(false);
          }
        }
      )
    )
    .subscribe();
  }

  ngOnDestroy(){
    localStorage.removeItem('token');
  }
}

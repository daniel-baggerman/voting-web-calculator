import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, ParamMap, UrlSegment, Router } from '@angular/router';
import { http_response } from '../shared/http_response.model';
import { ManageElectionService } from './manage-election.service';
import { DBTransactions } from '../db_transactions.service';

@Component({
  selector: 'app-election-workspace',
  templateUrl: './election-workspace.component.html',
  styleUrls: ['./election-workspace.component.css']
})
export class ElectionWorkspaceComponent implements OnInit {
  valid_election_url: boolean;
  cast_vote_mode = true;

  constructor(private route: ActivatedRoute,
              private election_manager: ManageElectionService,
              private trans: DBTransactions,
              private router: Router) { }

  ngOnInit() {
    //
    //  Check that the election in the URL navigation is valid.
    //
    let url_election_name = '';
    this.route.paramMap.subscribe(
      (paramMap: ParamMap) => {
        if (paramMap.has('election_name')){
          url_election_name = paramMap.get('election_name');
          
          // Check if we are only at the election workspace page without any component loaded. If so, nav to cast vote component.
          let url = location.pathname.split('/');
          if (url_election_name == url[url.length-1]){
            this.router.navigate(['vote'], {relativeTo: this.route});
          }
        }
      }
    );

    // get the election information passed when coming from search_elections and store it.
    let ls_state = history.state;

    // if state null then have to retrieve necessary information from the database based on url_election_name. 
    // This would happen if the user navigated here by typing the URL into their browser's navigation bar.
    if(ls_state.election_id == null){
      this.trans.get_election_from_url_name(url_election_name)
        .subscribe(
          (http_response: http_response) => {
            if(http_response.data && http_response.data.length !==0){
              ls_state = { election_id: http_response.data[0].election_id, name: http_response.data[0].name, description: http_response.data[0].description };
              this.election_manager.election = { election_id: ls_state.election_id, name: ls_state.name, description: ls_state.description, url_election_name: url_election_name };
              this.valid_election_url = true;
            } else {
              this.valid_election_url = false;
            }
          },
          (error) => {
            this.valid_election_url = false;
            alert(error.error.text);
            console.error(error);
          }
        );
    } else {
      this.valid_election_url = true;
      this.election_manager.election = { election_id: ls_state.election_id, name: ls_state.name, description: ls_state.description, url_election_name: url_election_name };
    }
  }

}

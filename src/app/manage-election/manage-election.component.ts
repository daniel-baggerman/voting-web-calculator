import { Component, OnInit } from '@angular/core';
import { Params, ActivatedRoute } from '@angular/router';
import { ManageElectionService } from './manage-election.service';
import { DBTransactions } from '../db_transactions.service';
import { http_response } from '../shared/http_response.model';

@Component({
  selector: 'app-manage-election',
  templateUrl: './manage-election.component.html',
  styleUrls: ['./manage-election.component.css']
})
export class ManageElectionComponent implements OnInit {
  show_election_details = false;
  valid_election_url: boolean;

  constructor(private route: ActivatedRoute,
              private election_manager: ManageElectionService,
              private trans: DBTransactions) { }

  ngOnInit() {
    let url_election_name = '';
    this.route.params.subscribe(
      (params: Params) => {
        url_election_name = params['election_name'];
      }
    );

    // get the election information passed when coming from search_elections and store it.
    let ls_state = history.state;

    // if state null then have to retrieve necessary information from the database based on url_election_name. This would happen if the user navigated here by typing the URL into their browser's navigation bar.
    if(ls_state.election_id == null){
      this.trans.get_election_from_url_name(url_election_name)
        .subscribe(
          (http_response: http_response) => {
            if(http_response.data){
              console.log(http_response.data);
              ls_state = { election_id: http_response.data[0].election_id, description: http_response.data[0].description };
              console.log(ls_state);
              this.valid_election_url = true;
            } else {
              this.valid_election_url = false;
            }
          },
          (error) => {
            this.valid_election_url = false;
            alert(error.message);
            console.error(error);
          }
        );
    } else {
      this.valid_election_url = true;
    }

    this.election_manager.election = { election_id: ls_state.election_id, description: ls_state.description, url_election_name: url_election_name };
    console.log(this.election_manager.election);
  }
}

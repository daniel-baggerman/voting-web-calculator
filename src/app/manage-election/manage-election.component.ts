import { Component, OnInit } from '@angular/core';
import { Params, ActivatedRoute } from '@angular/router';
import { ManageElectionService } from './manage-election.service';

@Component({
  selector: 'app-manage-election',
  templateUrl: './manage-election.component.html',
  styleUrls: ['./manage-election.component.css']
})
export class ManageElectionComponent implements OnInit {
  show_election_details = false;

  constructor(private route: ActivatedRoute,
              private election_manager: ManageElectionService) { }

  ngOnInit() {
    // get the election information passed when coming from search_elections and store it.
    let ls_state = history.state;
    if(ls_state == null){
      // if state null then have to retrieve necessary information from the database based on url_election_name. This would happen if the user navigated here by typing the URL into their browser's navigation bar.
    }

    let url_election_name = '';
    this.route.params.subscribe(
      (params: Params) => {
        url_election_name = params['election_id'];
      }
    );

    this.election_manager.election = { election_id: ls_state.election_id, description: ls_state.description, url_election_name: url_election_name };
    
  }

  election_selected(election_id: number){
    // show election details
    this.show_election_details = true;
  }

}

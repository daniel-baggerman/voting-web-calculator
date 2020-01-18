import { Component, OnInit } from '@angular/core';
import { Params, ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app-manage-election',
  templateUrl: './manage-election.component.html',
  styleUrls: ['./manage-election.component.css']
})
export class ManageElectionComponent implements OnInit {
  show_election_details = false;

  constructor(private route: ActivatedRoute) { }

  ngOnInit() {
    this.route.params.subscribe(
      (params: Params) => {
        if (params['election_id']){
          this.election_selected(params['election_id']);
        } else {
          console.log('test');
          this.show_election_details = false;
        }
      }
    );
  }

  election_selected(election_id: number){
    // show election details
    this.show_election_details = true;
  }

}

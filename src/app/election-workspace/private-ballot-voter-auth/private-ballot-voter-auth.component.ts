import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, ParamMap, ActivatedRouteSnapshot } from '@angular/router';

@Component({
  selector: 'app-private-ballot-voter-auth',
  templateUrl: './private-ballot-voter-auth.component.html',
  styleUrls: ['./private-ballot-voter-auth.component.css']
})
export class PrivateBallotVoterAuthComponent implements OnInit {

  constructor(private route_snapshot: ActivatedRouteSnapshot) { }

  ngOnInit() {
    let ls_url_election_name: string,
        ls_ballot_code: string;

    // Grab and store the election_name
    if (this.route_snapshot.paramMap.has('election_name')){
      ls_url_election_name = this.route_snapshot.paramMap.get('election_name');
    }

    // Grab and store the ballot code
    if (this.route_snapshot.paramMap.has('ballot_code')){
      ls_ballot_code = this.route_snapshot.paramMap.get('ballot_code');
    }

    // auth http request
  }

}

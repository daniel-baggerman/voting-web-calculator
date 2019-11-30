import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { bpOption } from './beatpath/bp_models/bp_option.model';
import { NgForm } from '@angular/forms';
import { election } from './shared/election.model';

@Injectable({providedIn: 'root'})
export class DBTransactions {
    constructor(private http: HttpClient){}

    get_elections(){
        return this.http.get("../backend/get_elections.php");
    }

    get_election_options(election_id: number){
        return this.http.get("../backend/load_ballot_info_v2.php?election_id="+election_id);
    }

    submit_ballot(election_id: number, voter_id: number, selected_options: bpOption[]){
        let options = JSON.stringify(Object.assign({},selected_options));
        // options is an array of the bpOption objects taken from the selected_options in the bp-ballot service
        return this.http.post("../backend/submit_ballot.php?election_id="+election_id+"&voter_id="+voter_id,options);
    }

    create_election(election: election){

    }
}
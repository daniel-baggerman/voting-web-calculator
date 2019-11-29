import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { bpOption } from './beatpath/bp_models/bp_option.model';

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
        console.log("selected options");
        console.log(selected_options);
        let options = JSON.stringify(Object.assign({},selected_options));
        console.log("stringify options\r\n"+options);
        // options is an array of the bpOption objects taken from the selected_options in the bp-ballot service
        return this.http.post("../backend/submit_ballot.php?election_id="+election_id+"&voter_id="+voter_id,options);
    }
}
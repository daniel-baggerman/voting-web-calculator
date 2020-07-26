import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { bpOption } from '../election-workspace/beatpath/bp_models/bp_option.model';
import { http_response } from 'src/app/core/models/http_response.model';
import { GlobalConstants } from './models/global-constants';

@Injectable({providedIn: 'root'})
export class DBTransactions {
    constructor(private http: HttpClient){}

    get_elections(){
        return this.http.get(GlobalConstants.apiURL+"get_elections.php");
    }

    get_election_options(election_id: number){
        return this.http.get<http_response>(GlobalConstants.apiURL+"load_ballot_info_v2.php?election_id="+election_id);
    }

    submit_ballot(election_id: number, selected_options: bpOption[]){
        let options = JSON.stringify(Object.assign({},selected_options));
        // options is an array of the bpOption objects taken from the selected_options in the bp-ballot service
        return this.http.post<http_response>(GlobalConstants.apiURL+"submit_ballot.php?election_id="+election_id,options);
    }

    create_election(election: object){
        let ls_election = JSON.stringify(election);
        // console.log(ls_election);
        return this.http.post<http_response>(GlobalConstants.apiURL+"create_election.php",ls_election);
    }

    get_elections_like(url_election_name: string){
        return this.http.get(GlobalConstants.apiURL+"get_elections_like.php?string="+url_election_name);
    }

    get_election_from_url_name(url_election_name: string){
        return this.http.get(GlobalConstants.apiURL+"get_election_from_url_name.php?url_election_name="+url_election_name);
    }

    calc_election(election_id: number){
        return this.http.get<http_response>(GlobalConstants.apiURL+"winner_calc.php?election_id="+election_id);
    }

    get_paths_and_labels(election_id: number){
        return this.http.get<http_response>(GlobalConstants.apiURL+"get_paths_and_labels.php?election_id="+election_id);
    }
}
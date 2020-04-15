import { bpOption } from '../election-workspace/beatpath/bp_models/bp_option.model';

export class election{
    constructor(public election_id: number,
                public name: string,
                public description: string,
                public options?: string,
                public public_private?: number,  // 1 or 0, 1 = public
                public start_date?: string,
                public end_date?: string,
                public password_protect?: number, // 1 or 0, 1 = yes
                public password?: string,
                public url_election_name?: string,
                public anon_results?: number,    // 1 or 0, 1 = yes
                ){
    }
}
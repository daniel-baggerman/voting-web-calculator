import { bpOption } from '../beatpath/bp_models/bp_option.model';

export class election{
    constructor(public election_id: number, 
                public description: string,
                public options?: string,
                public public_private?: number,  // 1 or 0, 1 = public
                public anon_results?: number,    // 1 or 0, 1 = yes
                public start_time?: string,
                public halt_time?: string,
                public password?: string
                ){
    }
}
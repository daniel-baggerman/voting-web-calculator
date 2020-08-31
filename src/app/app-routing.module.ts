import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { HomePageComponent } from './home-page/home-page.component';
import { CreateElectionComponent } from './create-election/create-election.component';
import { PageNotFoundComponent } from './page-not-found/page-not-found.component';
import { SearchElectionsComponent } from './search-elections/search-elections.component';
import { ManageElectionComponent } from './election-workspace/manage-election/manage-election.component';
import { ReportingComponent } from './election-workspace/reporting/reporting.component';
import { ElectionWorkspaceComponent } from './election-workspace/election-workspace.component';
import { BeatpathBallotCastComponent } from './election-workspace/beatpath/beatpath-ballot-cast/beatpath-ballot-cast.component';
import { VoterAuthenticationComponent } from './election-workspace/voter_auth/voter-authentication.component';
import { PrivateBallotVoterAuthComponent } from './election-workspace/private-ballot-voter-auth/private-ballot-voter-auth.component';
import { HowItWorksComponent } from './how-it-works/how-it-works.component';
import { RankedChoiceBallotComponent } from './how-it-works/ranked-choice-ballot/ranked-choice-ballot.component';
import { TallyMethodComponent } from './how-it-works/tally-method/tally-method.component';
import { AboutSecurityComponent } from './how-it-works/about-security/about-security.component';
import { AuthGuard } from './core/auth-guard/auth.guard';
import { CookieComponent } from './cookie/cookie.component';

const appRoutes: Routes = [
    { path: '', component: HomePageComponent },
    { path: 'election_search', component: SearchElectionsComponent },
    { path: 'how_it_works', component: HowItWorksComponent },
    { path: 'create_election', component: CreateElectionComponent },
    { path: 'cookie', component: CookieComponent },
    { path: 'how_it_works', component: HowItWorksComponent,
        children: [
            { path: 'ranked_choice_ballot', component: RankedChoiceBallotComponent },
            { path: 'tally_method', component: TallyMethodComponent },
            { path: 'about_security', component: AboutSecurityComponent }
        ]
    },
    { path: ':election_name', component: ElectionWorkspaceComponent,
        children: [
            { path: 'vote', component: BeatpathBallotCastComponent, canActivate: [AuthGuard] },
            { path: 'results', component: ReportingComponent },
            { path: 'manage', component: ManageElectionComponent },
            { path: 'authenticate', component: VoterAuthenticationComponent },
            { path: ':ballot_code', component: PrivateBallotVoterAuthComponent }
        ]
    },
    { path: '**', component: PageNotFoundComponent }
]

@NgModule({
    imports: [RouterModule.forRoot(appRoutes, {useHash: false})],
    exports: [RouterModule]
})
export class AppRoutingModule { } 
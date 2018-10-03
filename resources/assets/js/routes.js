import Home from './views/Welcome';
import Report from './views/Report';
import NegativeLoans from './views/NegativeLoans';
import Loans from './views/loans/Index';
import LoansInArrears from './views/loans/LoansInArrears';
import PartialDeaultLoans from './views/loans/PartialDeaultLoans';
import LoansCommand from './views/loans/IndexCommand';
import Amortization from './views/Amortization';

export const routes = [
    {
        path: '/',
        name: 'home',
        component: Home
    },
    {
        path: '/report',
        name: 'report',
        component: Report
    },
    {
        path: '/negative_loans',
        name: 'negavitve_loans',
        component: NegativeLoans
    },
    {
        path: '/loans',
        name: 'loans',
        component: Loans
    },
    {
        path: '/partial_loans',
        name: 'partial_loans',
        component: PartialDeaultLoans
    },
    {
        path: '/loans_in_arriears',
        name: 'loans_in_arriears',
        component: LoansInArrears
    },
    {
        path: '/loans_command',
        name: 'loans_command',
        component: LoansCommand
    },
    {
        path: '/amortization',
        name: 'amortization',
        component: Amortization
    },
];
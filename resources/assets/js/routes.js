import Home from './views/Welcome';
import Report from './views/Report';
import NegativeLoans from './views/NegativeLoans';
import Loans from './views/loans/Index';

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
];
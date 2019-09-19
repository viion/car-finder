import { HashRouter as Router, Route, NavLink, Redirect } from "react-router-dom";
import React, { useState, useEffect } from 'react';

import Cars from "./pages/cars";
import Alerts from "./pages/alerts";

export default function Routing() {
    useEffect(() => {
        // --
    });

    return (
        <div className="site">
            <Router>
                <nav>
                    <NavLink to="/">
                        Cars
                    </NavLink>
                    <NavLink to="/alerts">
                        Alerts
                    </NavLink>
                </nav>
                <main>
                    <Route exact
                           path="/"
                           render={
                               (props) => <Cars {...props} />
                           }
                    />
                    <Route exact
                           path="/alerts"
                           render={
                               (props) => <Alerts {...props} />
                           }
                    />
                </main>
            </Router>
        </div>
    );
}

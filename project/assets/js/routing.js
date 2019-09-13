import { HashRouter as Router, Route, NavLink, Redirect } from "react-router-dom";
import React, { useState, useEffect } from 'react';

import Home from "./pages/home";
import TestPage from "./pages/test-page";

export default function Routing() {
    useEffect(() => {
        // --
    });

    return (
        <div className="site">
            <Router>
                <nav>
                    <NavLink to="/" className="nav-link">
                        Home
                    </NavLink>
                    <NavLink to="/test" className="nav-link">
                        Test
                    </NavLink>
                </nav>
                <main>
                    <Route exact path="/" component={Home} />
                    <Route exact
                           path="/test"
                           render={
                               (props) => <TestPage {...props} />
                           }
                    />
                </main>
            </Router>
        </div>
    );
}

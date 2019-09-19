import React, { useState, useEffect } from 'react';
import API from '../api/api';

export default function Cars() {
    const [cars, setCars] = useState([]);

    useEffect(() => {
        loadCars();
    });

    function loadCars() {
        if (cars.length > 0) {
            return;
        }

        API.getCars(setCars)
    }

    return (
        <div>
            {
                (cars.length === 0) && <div>
                    <p>
                        Loading Cars
                    </p>
                </div>
            }
            <div className="car-list">

                {
                    cars.map((car, i) => {
                        const url = "https://www.autotrader.co.uk/classified/advert/" + car.siteId;

                        return (
                            <div key={i} className="car">
                                <div className="car-pic" style={{ backgroundImage: "url("+ car.images[0] +")" }}>
                                    <span className={car.score > 0 ? 'green' : 'red'}>{car.score}</span>
                                </div>
                                <div>
                                    <a href={url} target="_blank">
                                        <h3><strong>£{car.price}</strong> &nbsp;&nbsp; {car.title}</h3>
                                    </a>
                                    <div className="car-info">
                                        <span><strong>Valuation:</strong> {car.priceValuation}</span>
                                        <span><strong>Year:</strong> {car.year}</span>
                                        <span><strong>Tax:</strong> £{car.tax}</span>
                                        <span><strong>Fuel:</strong> {car.fuel}</span>
                                        <span><strong>Gearbox:</strong> {car.gearbox}</span>
                                        <span><strong>Miles:</strong> {car.miles}</span>
                                        <span><strong>Engine:</strong> {car.engineSize}</span>
                                        <span><strong>Checks:</strong> {car.checkStatus}</span>
                                        <span><strong>Seller:</strong> {car.sellerName} ({car.sellerRating > 0 ? car.sellerRating : 'No Rating'} - {car.sellerReviews > 0 ? car.sellerReviews : 'No' } reviews)</span>
                                    </div>
                                    <div className="car-info">
                                        {
                                            car.scoredata && Object.keys(car.scoredata).map((ScoreField, i) => {
                                                const ScoreValue = car.scoredata[ScoreField];

                                                return (
                                                    <span key={ScoreField}><strong>{ScoreField}</strong>: {ScoreValue}</span>
                                                )
                                            })
                                        }
                                    </div>
                                    <div className="car-photos">
                                        {
                                            car.images.map((img, i) => {
                                                return (
                                                    <a key={i} href={img} target="_blank">
                                                        <img src={img} />
                                                    </a>
                                                )
                                            })
                                        }
                                    </div>
                                </div>
                            </div>
                        )
                    })
                }
            </div>
        </div>
    )
}

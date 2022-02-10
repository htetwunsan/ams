import React, { useState, useEffect } from 'react';
import Header from '../components/Header';
import TopMovieCarousel from '../components/TopMovieCarousel';
import MovieList from '../components/MovieList';
import MovieDetail from '../components/MovieDetail';
import MainRightContent from '../components/MainRightContent';
import NavFilter from '../components/NavFilter';

export const HomeContext = React.createContext({});

export default function HomePage() {

    const [selectedMovie, setSelectedMovie] = useState(JSON.parse(sessionStorage.getItem('selectedMovie')));
    const [filter, setFilter] = useState(selectedMovie ? 'none' : 'sub');
    const [paginator, setPaginator] = useState(null);
    const [keyword, setKeyword] = useState("");

    const overrideSetFilter = (filter) => {
        setFilter(filter);
        setSelectedMovie(null);
    };

    const overrideSetSelectedMovie = (movie) => {
        if (movie) {
            setFilter('none');
        }
        setSelectedMovie(movie);
    };

    useEffect(() => {
        sessionStorage.setItem('selectedMovie', JSON.stringify(selectedMovie));
    }, [selectedMovie]);

    return (
        <HomeContext.Provider value={{ selectedMovie: selectedMovie, setSelectedMovie: overrideSetSelectedMovie }}>
            <div className="bg-slate-900 text-slate-100 flex-grow flex flex-col items-stretch mb-8">
                <Header setFilter={overrideSetFilter} setPaginator={setPaginator} keyword={keyword} setKeyword={setKeyword} />
                <TopMovieCarousel />
                <div className="flex-grow flex mt-2">
                    <div className="basis-3/4 flex flex-col items-stretch">
                        <div className="flex flex-col items-stretch px-6">
                            <NavFilter filter={filter} setFilter={overrideSetFilter} />
                            {
                                (filter === 'search' && paginator) &&
                                <blockquote className="flex flex-col items-stretch text-base font-bold border-l-4 border-sky-400 italic p-2 ml-2">
                                    Search results
                                </blockquote>
                            }
                        </div>
                        <main className="flex-grow flex flex-col items-stretch px-6 py-4">
                            {!selectedMovie && <MovieList filter={filter} paginator={paginator} setPaginator={setPaginator} />}
                            {selectedMovie && <MovieDetail movie={selectedMovie} />}
                        </main>

                    </div>
                    <div className="basis-0 md:basis-1/4 flex-none flex flex-col items-stretch">
                        <MainRightContent />
                    </div>
                </div>
            </div>
        </HomeContext.Provider>
    );
}

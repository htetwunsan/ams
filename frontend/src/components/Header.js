import React, { useRef } from 'react';
import repository from '../repository';
import logo from '../logo.svg';

export default function Header({ setFilter, setPaginator, keyword, setKeyword }) {
    const ref = useRef(null);

    const handleSubmit = e => {
        e.preventDefault();
        setPaginator(null);
        setFilter('search');
        ref.current?.blur();
        repository.search(keyword).then(response => {
            setPaginator(response.data);
        }).catch(reason => console.log(reason));
    };

    const handleClickLogo = e => {
        setFilter('sub');
    };

    return (
        <div className="flex flex-col items-stretch border-b border-sky-400">
            <div className="flex items-center justify-between py-2 px-4">
                <button className="flex items-center justify-center gap-x-4"
                    onClick={handleClickLogo}>
                    <img src={logo} className="w-16 h-16" alt="logo" />
                    <div className="flex flex-col items-stretch">
                        <h1 className="text-4xl font-medium tracking-wider">AMS</h1>
                        <h6 className="text-xs font-light leading-none tracking-tighter">Asian movies & series</h6>
                    </div>
                </button>
                <div className="basis-1/4 flex-none flex flex-col items-stretch hover:basis-2/4 lg:hover:basis-1/3 duration-300">
                    <form className="bg-slate-900 flex flex-col items-stretch" onSubmit={handleSubmit}>
                        <input
                            ref={ref}
                            className="appearance-none w-full h-10 px-4 text-sm text-slate-100 bg-slate-900 border border-gray-600 rounded-lg focus:border-sky-400 focus:outline-none focus:ring-0"
                            type="search"
                            value={keyword}
                            onChange={e => setKeyword(e.target.value)}
                            placeholder="Search" required />
                    </form>
                </div>
            </div>
        </div>
    );
}

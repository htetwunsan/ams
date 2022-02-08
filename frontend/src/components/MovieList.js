import clsx from 'clsx';
import React, { useEffect } from 'react';
import repository from '../repository';
import MovieItem from './MovieItem';
import ContentLoader from 'react-content-loader';
import Pagination from './Pagination';

export default function MovieList({ filter, paginator, setPaginator }) {

    useEffect(() => {
        if (filter === 'search') return;
        setPaginator(null);
        const controller = new AbortController();
        repository.getList(filter, controller).then(response => {
            setPaginator(response.data);
        }).catch(reason => {
            console.log(reason);
        });
        return () => {
            controller.abort();
        };
    }, [filter]);

    return (
        <div className="flex flex-col items-stretch">
            <ul className="list-none flex flex-col items-stretch">
                {
                    !paginator && [1, 2, 3, 4, 5].map(num => (<li key={num} className="flex flex-col items-stretch py-2">
                        <ContentLoader
                            backgroundColor={"#0f172a"}
                            style={{ width: "100%" }}
                        >
                            <rect x="10" y="0" rx="5" ry="5" width="100" height="120" />
                            <rect x="120" y="0" rx="0" ry="0" width="150" height="20" />
                            <rect x="120" y="30" rx="0" ry="0" width="150" height="70" />
                            <rect x="120" y="110" rx="0" ry="0" width="150" height="10" />
                        </ContentLoader>
                    </li>))
                }
                {
                    paginator?.data?.map((movie, index) => (
                        <li className={clsx("flex flex-col items-stretch", index !== paginator.data.length - 1 && "border-b border-slate-900")} key={movie.slug}>
                            <MovieItem movie={movie} />
                        </li>
                    ))
                }
            </ul>
            <div className="flex flex-col items-stretch m-2">
                {
                    paginator && <Pagination filter={filter} paginator={paginator} setPaginator={setPaginator} />
                }
            </div>
        </div>
    );
}

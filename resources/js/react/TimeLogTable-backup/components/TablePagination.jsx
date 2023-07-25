import * as React from 'react';
import styled from 'styled-components'


const Pagination = ({
    data,
    nPageRows,
    currentPage,
    setCurrentPage,
    setNPageRows,
    totalRows,
}) => {
    const [pageNumbers, setPageNumbers] = React.useState([]);
    const [renderButtons, setRenderButtons] = React.useState([]);
    const [totalPages, setTotalPages] = React.useState(0);

    
    const isTotalPagesChange = React.useMemo(() => totalPages, [totalPages])

    // count total pages
    React.useEffect(() => {
        setCurrentPage(1);
        const tPages = Math.ceil(totalRows / nPageRows);
        setTotalPages(tPages);
    }, [totalRows, nPageRows]);

    // render buttons
    React.useEffect(() => {
        const buttons = [];

        if(totalPages <= 7){
            for (let i = 1; i <= totalPages; i++) {
                buttons.push(i);
            }
        }else{ 
            if (currentPage <= 3) {
                for (let i = 1; i < 5; i++) {
                    buttons.push(i);
                }
                
            }else if (currentPage >= totalPages - 3) {
                for (let i = totalPages - 4; i <= totalPages; i++) {
                    buttons.push(i);
                }
            }else if (currentPage > 3 && currentPage < totalPages - 3) {
                for (let i = currentPage - 2; i <= currentPage + 2; i++) {
                    buttons.push(i);
                }
            }
        }

        setRenderButtons([...buttons]);
    }, [totalPages, currentPage, isTotalPagesChange]);


    // total page
    React.useEffect(() => {
        const pageNumbers = [];
        if(totalRows < 0) return;
        for (let i = 1; i <= Math.ceil(totalRows / nPageRows); i++) {
            pageNumbers.push(i);
        }
        setPageNumbers(pageNumbers);
    }, [totalRows, nPageRows]);

    
    const handleClick = (e) => {
        setCurrentPage(Number(e.target.id));
    };

    const previousPage = () => {
        if (currentPage > 1) {
            setCurrentPage(currentPage - 1);
        }
    };

    const nextPage = () => {
        if (currentPage < pageNumbers.length) {
            setCurrentPage(currentPage + 1);
        }
    };

    const handleSelectChange = (e) => {
        setNPageRows(e.target.value);
    };




    
    return (
        <PaginationContainer>
            <div>
                <label htmlFor="nPageRows">Show</label>
                <SelectParPage
                    name="nPageRows"
                    id="nPageRows"
                    onChange={handleSelectChange}
                >
                    <option value={10}>10</option>
                    <option value={20}>20</option>
                    <option value={30}>30</option>
                    <option value={40}>40</option>
                </SelectParPage>
                <label htmlFor="nPageRows">entries</label>
            </div>
            <PaginationGroup>
                <EntriesPerPage>
                    Showing {currentPage * nPageRows - nPageRows + 1} to{" "}
                    {currentPage * nPageRows > totalRows
                        ? totalRows
                        : currentPage * nPageRows}{" "}
                    of {totalRows} entries
                </EntriesPerPage>
                <PaginationButtons>
                    <PreviousBtn
                        disabled={currentPage === 1 ? true : false}
                        onClick={previousPage}
                    >
                        Previous
                    </PreviousBtn>
                    {totalPages > 0 && (
                        <>
                            {
                                
                                renderButtons[0] > 1 && (
                                    <>
                                    <PaginateNumber
                                        id={1}
                                        onClick={handleClick}
                                        className={currentPage === 1 ? "active" : ""}
                                    >
                                       1
                                    </PaginateNumber>
                                    <PaginateNumber>...</PaginateNumber>
                                    </>
                                )
                            }
                            {renderButtons?.map((number) => (
                                <React.Fragment key={number}>
                                    <PaginateNumber
                                        id={number}
                                        onClick={handleClick}
                                        className={
                                            currentPage === number
                                                ? "active"
                                                : ""
                                        }
                                    >
                                        {number}
                                    </PaginateNumber>
                                </React.Fragment>
                            ))}

                            {
                                // render dots
                                renderButtons[renderButtons.length - 1] <
                                totalPages - 1 && (
                                    <>
                                        <PaginateNumber>...</PaginateNumber>
                                        <PaginateNumber
                                            id={totalPages}
                                            onClick={handleClick}
                                            className={currentPage === totalPages ? "active" : ""}
                                        >
                                            {totalPages}
                                        </PaginateNumber>
                                    </>
                                )
                            }
                        </>
                    )}

                    <NextBtn
                        disabled={
                            currentPage === pageNumbers.length ? true : false
                        }
                        onClick={nextPage}
                    >
                        Next
                    </NextBtn>
                </PaginationButtons>
            </PaginationGroup>
        </PaginationContainer>
    );
};

export default Pagination;

// styled
const PaginationContainer = styled.div`
    width: 100%;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    align-items: center;
    padding: 20px;
    box-sizing: border-box;
    font-size: 14px;
`;

const SelectParPage = styled.select`
    padding: 4px;
    font-size: 12px;
    border-radius: 5px;
    border: 1px solid #eaf0f7;
    color: rgb(0 0 0 / 60%);
    background: #fff;
    margin: 0 6px;
    option {
        padding: 6px;
        font-size: 12px;
        border-radius: 5px;
    }

    &:focus {
        outline: none;
    }
`;

const PaginationGroup = styled.div`
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
`;

const EntriesPerPage = styled.div`
  color: rgb(0 0 0 / 40%)
  margin-right: 10px;
  font-size: 14px;
  margin-right: 10px;
`;

const PaginationButtons = styled.div`
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 10px 0;
`;
const PreviousBtn = styled.button`
    padding: 6px;
    font-size: 12px;
    border-radius: 5px;
    border: 1px solid #eaf0f7;
    color: #000;
    background: #fff;
    &:hover {
        background: #eaf0f7;
        color: #1d82f5;
    }
    &:active {
        background: #1d82f5;
        color: #fff;
    }
    &:disabled {
        background: #f3f3f3;
        color: #ccc;
    }
`;

const NextBtn = styled.button`
    padding: 6px;
    font-size: 12px;
    border-radius: 5px;
    border: 1px solid #eaf0f7;
    color: #000;
    background: #fff;
    &:hover {
        background: #eaf0f7;
        color: #1d82f5;
    }
    &:active {
        background: #1d82f5;
        color: #fff;
    }
    &:disabled {
        background: #f3f3f3;
        color: #ccc;
    }
`;
// pagination styled
const PaginateNumber = styled.div`
    width: 16px;
    height: 16px;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 16px;
    margin: 0 6px;
    border: none;
    font-size: 14px;
    background: ${(props) =>
        props.className === "active" ? "#1d82f5" : "#fff"};
    color: ${(props) => (props.className === "active" ? "#fff" : "#000")};
    cursor: pointer;
    border-radius: 5px;
    border: 1px solid #eaf0f7;
    &:hover {
        background: #eaf0f7;
        color: #1d82f5;
    }
`;